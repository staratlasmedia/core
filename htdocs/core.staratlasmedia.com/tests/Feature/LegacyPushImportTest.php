<?php

namespace Tests\Feature;

use App\Models\LegacyPushApp;
use App\Models\PushSubscription;
use App\Models\VapidKeySet;
use App\Services\LegacyPush\LegacyPushImportService;
use App\Services\LegacyPush\LegacyPushTokenParser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LegacyPushImportTest extends TestCase
{
    use RefreshDatabase;

    private string $legacyDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->legacyDatabase = tempnam(sys_get_temp_dir(), 'legacy_push_');

        config()->set('database.connections.legacy_push', [
            'driver' => 'sqlite',
            'database' => $this->legacyDatabase,
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);

        DB::purge('legacy_push');
        $this->createLegacySchema();
        $this->seedGlobalVapid();
    }

    protected function tearDown(): void
    {
        DB::disconnect('legacy_push');

        if (isset($this->legacyDatabase) && is_file($this->legacyDatabase)) {
            unlink($this->legacyDatabase);
        }

        parent::tearDown();
    }

    public function test_token_parser_accepts_flat_and_nested_key_shapes(): void
    {
        $parser = new LegacyPushTokenParser;
        $flat = $parser->parse(json_encode([
            'endpoint' => 'https://push.example/flat',
            'p256dh' => 'flat-p256dh',
            'auth' => 'flat-auth',
        ], JSON_THROW_ON_ERROR));
        $nested = $parser->parse(json_encode([
            'endpoint' => 'https://push.example/nested',
            'keys' => [
                'p256dh' => 'nested-p256dh',
                'auth' => 'nested-auth',
            ],
        ], JSON_THROW_ON_ERROR));

        $this->assertSame(hash('sha256', 'https://push.example/flat'), $flat['endpoint_hash']);
        $this->assertSame('nested-p256dh', $nested['p256dh']);
        $this->assertNull($parser->parse('not-json'));
    }

    public function test_dry_run_reports_safe_counts_without_printing_secrets(): void
    {
        $this->seedPlatform(1, shared: false);
        $this->seedDevice(appid: 1, platid: 5, endpoint: 'https://push.example/subscription');

        Artisan::call('core:legacy-push:import', [
            '--dry-run' => true,
            '--appids' => '1',
        ]);

        $output = Artisan::output();

        $this->assertStringContainsString('planned_inserts', $output);
        $this->assertStringContainsString(hash('sha256', 'https://push.example/subscription'), $output);
        $this->assertStringNotContainsString('https://push.example/subscription', $output);
        $this->assertStringNotContainsString('p256dh-secret', $output);
        $this->assertStringNotContainsString('auth-secret', $output);
        $this->assertStringNotContainsString('private-app-1', $output);
    }

    public function test_import_creates_encrypted_pending_records_idempotently(): void
    {
        $this->seedPlatform(1, shared: false);
        $this->seedDevice(appid: 1, platid: 5, endpoint: 'https://push.example/subscription');

        Artisan::call('core:legacy-push:import', [
            '--appids' => '1',
        ]);
        Artisan::call('core:legacy-push:import', [
            '--appids' => '1',
        ]);

        $this->assertSame(1, PushSubscription::query()->count());
        $this->assertSame(1, VapidKeySet::query()->count());

        $subscription = PushSubscription::query()->firstOrFail();
        $vapid = VapidKeySet::query()->firstOrFail();

        $this->assertSame('legacy_import', $subscription->source);
        $this->assertSame('legacy_import_pending', $subscription->status);
        $this->assertSame('https://push.example/subscription', $subscription->endpoint_encrypted);
        $this->assertSame('private-app-1', $vapid->private_key_encrypted);

        $rawEndpoint = DB::table('push_subscriptions')->whereKey($subscription->id)->value('endpoint_encrypted');
        $rawPrivateKey = DB::table('vapid_key_sets')->whereKey($vapid->id)->value('private_key_encrypted');

        $this->assertNotSame('https://push.example/subscription', $rawEndpoint);
        $this->assertNotSame('private-app-1', $rawPrivateKey);
    }

    public function test_import_filters_platforms_status_firebase_unmapped_and_malformed_rows(): void
    {
        $this->seedPlatform(1, shared: false);
        $this->seedDevice(appid: 1, platid: 5, endpoint: 'https://push.example/valid');
        $this->seedDevice(appid: 1, platid: 6, endpoint: 'https://push.example/safari');
        $this->seedDevice(appid: 1, platid: 5, endpoint: 'https://push.example/inactive', status: 0);
        $this->seedDevice(appid: 1, platid: 5, endpoint: 'https://push.example/firebase', firebase: 1);
        $this->seedDevice(appid: 1, platid: 5, token: 'malformed-json');
        $this->seedDevice(appid: 99, platid: 5, endpoint: 'https://push.example/unmapped');

        $report = app(LegacyPushImportService::class)->run(appids: [1, 99], dryRun: true);

        $this->assertSame(3, $report['eligible_rows']);
        $this->assertSame(1, $report['migrable_rows']);
        $this->assertSame(1, $report['malformed_rows']);
        $this->assertSame(1, $report['unmapped_rows']);
        $this->assertSame(1, $report['planned_inserts']);
    }

    public function test_clubalfa_mappings_keep_it_merged_and_en_separate(): void
    {
        foreach ([1, 11, 12] as $appid) {
            $this->seedPlatform($appid, shared: $appid === 12);
            $this->seedDevice(appid: $appid, platid: 5, endpoint: "https://push.example/{$appid}");
        }

        Artisan::call('core:legacy-push:import', [
            '--appids' => '1,11,12',
        ]);

        $apps = LegacyPushApp::query()
            ->orderBy('legacy_appid')
            ->get()
            ->keyBy('legacy_appid');

        $this->assertSame('clubalfa_it', $apps[1]->merge_group);
        $this->assertSame('clubalfa_it', $apps[1]->pushGroup->code);
        $this->assertSame('main', $apps[1]->section);
        $this->assertSame('clubalfa_it', $apps[11]->merge_group);
        $this->assertSame('clubalfa_it', $apps[11]->pushGroup->code);
        $this->assertSame('automobili', $apps[11]->section);
        $this->assertSame('/automobili/smart_sw.js', $apps[11]->service_worker_url);
        $this->assertSame('/automobili/', $apps[11]->service_worker_scope);
        $this->assertSame('clubalfa_en', $apps[12]->merge_group);
        $this->assertSame('clubalfa_en', $apps[12]->pushGroup->code);
        $this->assertSame('en', $apps[12]->section);
    }

    public function test_modern_dispatch_scope_excludes_legacy_pending_records(): void
    {
        $this->seedPlatform(1, shared: false);
        $this->seedDevice(appid: 1, platid: 5, endpoint: 'https://push.example/legacy');

        Artisan::call('core:legacy-push:import', [
            '--appids' => '1',
        ]);

        $legacy = PushSubscription::query()->firstOrFail();

        PushSubscription::query()->create([
            'site_id' => $legacy->site_id,
            'site_origin_id' => $legacy->site_origin_id,
            'source' => 'core_sdk',
            'status' => 'active',
            'origin' => 'https://www.clubalfa.it',
            'service_worker_url' => '/smart_sw.js',
            'service_worker_scope' => '/',
            'endpoint_hash' => hash('sha256', 'https://push.example/core'),
            'endpoint_encrypted' => 'https://push.example/core',
            'p256dh_encrypted' => 'core-p256dh',
            'auth_encrypted' => 'core-auth',
            'vapid_key_set_id' => $legacy->vapid_key_set_id,
        ]);

        $this->assertSame(1, PushSubscription::query()->modernDispatchEligible()->count());
    }

    private function createLegacySchema(): void
    {
        Schema::connection('legacy_push')->create('devices', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('userid');
            $table->unsignedInteger('appid');
            $table->unsignedTinyInteger('platid');
            $table->text('token');
            $table->string('md5_token')->default('');
            $table->unsignedTinyInteger('firebase')->default(0);
            $table->unsignedInteger('created_date')->default(0);
            $table->unsignedInteger('last_active_time')->nullable();
            $table->unsignedTinyInteger('status')->default(1);
        });

        Schema::connection('legacy_push')->create('apps_platfom', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('userid')->default(1);
            $table->unsignedInteger('appid');
            $table->unsignedTinyInteger('platid');
            $table->text('settings');
            $table->text('error_log')->nullable();
        });

        Schema::connection('legacy_push')->create('setting', function (Blueprint $table): void {
            $table->string('varname')->primary();
            $table->text('value');
        });
    }

    private function seedGlobalVapid(): void
    {
        DB::connection('legacy_push')->table('setting')->insert([
            ['varname' => 'vapid_public', 'value' => 'public-global'],
            ['varname' => 'vapid_private', 'value' => 'private-global'],
        ]);
    }

    private function seedPlatform(int $appid, bool $shared): void
    {
        DB::connection('legacy_push')->table('apps_platfom')->insert([
            'appid' => $appid,
            'platid' => 5,
            'settings' => serialize([
                'shared' => $shared ? 1 : 0,
                'vapid_public' => "public-app-{$appid}",
                'vapid_private' => "private-app-{$appid}",
            ]),
        ]);
    }

    private function seedDevice(
        int $appid,
        int $platid,
        ?string $endpoint = null,
        ?string $token = null,
        int $status = 1,
        int $firebase = 0,
    ): void {
        $token ??= json_encode([
            'endpoint' => $endpoint,
            'keys' => [
                'p256dh' => 'p256dh-secret',
                'auth' => 'auth-secret',
            ],
        ], JSON_THROW_ON_ERROR);

        DB::connection('legacy_push')->table('devices')->insert([
            'userid' => 10,
            'appid' => $appid,
            'platid' => $platid,
            'token' => $token,
            'md5_token' => md5($token),
            'firebase' => $firebase,
            'created_date' => 1_700_000_000,
            'last_active_time' => 1_700_000_100,
            'status' => $status,
        ]);
    }
}
