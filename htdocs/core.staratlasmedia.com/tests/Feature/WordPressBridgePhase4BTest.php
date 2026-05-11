<?php

namespace Tests\Feature;

use App\Filament\Resources\PluginUpdateDownloadResource;
use App\Models\BridgeInstallation;
use App\Models\BridgeSetupToken;
use App\Models\PluginPackage;
use App\Models\PluginRelease;
use App\Models\PluginUpdateDownload;
use App\Models\PushGroup;
use App\Models\Site;
use App\Models\SiteOrigin;
use App\Services\Bridge\BridgeConfigBuilder;
use App\Services\Bridge\BridgeTokenFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WordPressBridgePhase4BTest extends TestCase
{
    use RefreshDatabase;

    public function test_bridge_and_plugin_update_tables_exist(): void
    {
        foreach ([
            'bridge_setup_tokens',
            'bridge_installations',
            'bridge_config_versions',
            'plugin_packages',
            'plugin_releases',
            'plugin_update_downloads',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table [{$table}].");
        }

        $this->assertTrue(Schema::hasColumn('bridge_setup_tokens', 'token_hash'));
        $this->assertTrue(Schema::hasColumn('bridge_installations', 'bridge_secret_encrypted'));
        $this->assertTrue(Schema::hasColumn('bridge_installations', 'bridge_secret_fingerprint'));
        $this->assertTrue(Schema::hasColumn('plugin_releases', 'zip_sha256'));
        $this->assertSame('star-atlas-core-bridge', PluginPackage::query()->firstOrFail()->code);
        $this->assertFalse(PluginUpdateDownloadResource::canCreate());
        $this->assertFalse(PluginUpdateDownloadResource::canEdit(new PluginUpdateDownload));
    }

    public function test_setup_claim_consumes_token_once_and_returns_secret_only_once(): void
    {
        [$site, $origin] = $this->siteAndOrigin('/');
        $result = app(BridgeTokenFactory::class)->create($site, $site->pushGroup, $origin);
        $token = $result['token'];

        $rawHash = DB::table('bridge_setup_tokens')->whereKey($result['record']->id)->value('token_hash');

        $this->assertNotSame($token, $rawHash);
        $this->assertArrayNotHasKey('token_hash', $result['record']->fresh()->toArray());

        $response = $this->postJson('/api/bridge/setup/claim', [
            'setup_token' => $token,
            'wp_home_url' => 'https://www.clubalfa.it',
            'wp_site_url' => 'https://www.clubalfa.it',
            'detected_origin' => 'https://www.clubalfa.it',
            'detected_base_path' => '/',
            'wordpress_version' => '6.8',
            'php_version' => '8.3',
            'plugin_version' => '0.1.0',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('config.site_code', 'clubalfa_it')
            ->assertJsonPath('config.registration_service_worker_url', '/smart_sw.js')
            ->assertJsonStructure(['bridge_installation_id', 'bridge_secret', 'bridge_secret_fingerprint', 'config']);

        $installation = BridgeInstallation::query()->firstOrFail();
        $dbSecret = DB::table('bridge_installations')->whereKey($installation->id)->value('bridge_secret_encrypted');

        $this->assertNotSame($response->json('bridge_secret'), $dbSecret);
        $this->assertSame('consumed', BridgeSetupToken::query()->firstOrFail()->status);

        $this->postJson('/api/bridge/setup/claim', [
            'setup_token' => $token,
            'wp_home_url' => 'https://www.clubalfa.it',
            'detected_origin' => 'https://www.clubalfa.it',
            'detected_base_path' => '/',
        ])->assertUnprocessable();
    }

    public function test_claim_rejects_origin_mismatch(): void
    {
        [$site, $origin] = $this->siteAndOrigin('/');
        $token = app(BridgeTokenFactory::class)->create($site, $site->pushGroup, $origin)['token'];

        $this->postJson('/api/bridge/setup/claim', [
            'setup_token' => $token,
            'wp_home_url' => 'https://clubalfa.it',
            'detected_origin' => 'https://clubalfa.it',
            'detected_base_path' => '/',
        ])->assertUnprocessable();
    }

    public function test_hmac_protected_config_and_heartbeat_update_installation(): void
    {
        [$installation, $secret] = $this->claimedInstallation('/');

        $this->getJson('/api/bridge/config')->assertUnauthorized();

        $this->withHeaders($this->hmacHeaders($installation, $secret, 'GET', '/api/bridge/config'))
            ->getJson('/api/bridge/config')
            ->assertOk()
            ->assertJsonPath('config.site_code', 'clubalfa_it');

        $body = json_encode(['plugin_version' => '0.2.0'], JSON_THROW_ON_ERROR);

        $this->withHeaders($this->hmacHeaders($installation, $secret, 'POST', '/api/bridge/heartbeat', $body))
            ->json('POST', '/api/bridge/heartbeat', ['plugin_version' => '0.2.0'])
            ->assertOk()
            ->assertJsonPath('status', 'ok');

        $this->assertSame('0.2.0', $installation->fresh()->plugin_version);
    }

    public function test_config_builder_outputs_clubalfa_path_variants(): void
    {
        [$site, $root] = $this->siteAndOrigin('/');
        $automobili = $site->origins()->create([
            'origin' => 'https://www.clubalfa.it',
            'path_prefix' => '/automobili/',
        ]);
        $enGroup = PushGroup::query()->where('code', 'clubalfa_en')->firstOrFail();
        $enSite = Site::query()->create([
            'code' => 'clubalfa_en',
            'name' => 'ClubAlfa EN',
            'canonical_origin' => 'https://www.clubalfa.it',
            'language' => 'en',
            'push_group' => 'clubalfa_en',
            'push_group_id' => $enGroup->id,
        ]);
        $en = $enSite->origins()->create([
            'origin' => 'https://www.clubalfa.it',
            'path_prefix' => '/en/',
        ]);

        $builder = app(BridgeConfigBuilder::class);

        $rootConfig = $builder->previewForToken(app(BridgeTokenFactory::class)->create($site, $site->pushGroup, $root)['record']);
        $autoConfig = $builder->previewForToken(app(BridgeTokenFactory::class)->create($site, $site->pushGroup, $automobili, 'automobili')['record']);
        $enConfig = $builder->previewForToken(app(BridgeTokenFactory::class)->create($enSite, $enGroup, $en, 'en')['record']);

        $this->assertSame('/smart_sw.js', $rootConfig['registration_service_worker_url']);
        $this->assertSame('/automobili/', $autoConfig['wp_base_path']);
        $this->assertSame(['/automobili/smart_sw.js'], $autoConfig['local_service_worker_paths']);
        $this->assertSame('/en/smart_sw.js', $enConfig['registration_service_worker_url']);
        $this->assertSame('/en/pwa-start/?app=clubalfa_en', $enConfig['pwa_start_url']);
    }

    public function test_private_plugin_update_check_and_download_flow(): void
    {
        Storage::fake();

        [$installation, $secret] = $this->claimedInstallation('/');
        $package = PluginPackage::query()->firstOrFail();
        Storage::put('plugins/star-atlas-core-bridge-0.2.0.zip', 'zip-bytes');
        $release = PluginRelease::query()->create([
            'plugin_package_id' => $package->id,
            'version' => '0.2.0',
            'channel' => 'stable',
            'status' => 'published',
            'zip_storage_path' => 'plugins/star-atlas-core-bridge-0.2.0.zip',
            'zip_sha256' => hash('sha256', 'zip-bytes'),
            'published_at' => now(),
        ]);
        PluginRelease::query()->create([
            'plugin_package_id' => $package->id,
            'version' => '9.9.9',
            'channel' => 'stable',
            'status' => 'draft',
        ]);

        $response = $this->withHeaders($this->hmacHeaders($installation, $secret, 'GET', '/api/bridge/plugin/update-check'))
            ->getJson('/api/bridge/plugin/update-check')
            ->assertOk()
            ->assertJsonPath('update_available', true)
            ->assertJsonPath('version', '0.2.0');

        $this->assertSame(1, $release->downloads()->count());

        $this->get($response->json('download_url'))->assertOk();
        $this->assertSame('downloaded', PluginUpdateDownload::query()->firstOrFail()->status);
    }

    /**
     * @return array{Site, SiteOrigin}
     */
    private function siteAndOrigin(string $pathPrefix): array
    {
        $pushGroup = PushGroup::query()->where('code', 'clubalfa_it')->firstOrFail();
        $site = Site::query()->create([
            'code' => 'clubalfa_it',
            'name' => 'ClubAlfa IT',
            'canonical_origin' => 'https://www.clubalfa.it',
            'language' => 'it',
            'push_group' => 'clubalfa_it',
            'push_group_id' => $pushGroup->id,
        ]);
        $origin = $site->origins()->create([
            'origin' => 'https://www.clubalfa.it',
            'path_prefix' => $pathPrefix,
            'is_primary' => $pathPrefix === '/',
        ]);

        return [$site, $origin];
    }

    /**
     * @return array{BridgeInstallation, string}
     */
    private function claimedInstallation(string $pathPrefix): array
    {
        [$site, $origin] = $this->siteAndOrigin($pathPrefix);
        $token = app(BridgeTokenFactory::class)->create($site, $site->pushGroup, $origin)['token'];
        $response = $this->postJson('/api/bridge/setup/claim', [
            'setup_token' => $token,
            'wp_home_url' => 'https://www.clubalfa.it',
            'wp_site_url' => 'https://www.clubalfa.it',
            'detected_origin' => 'https://www.clubalfa.it',
            'detected_base_path' => $pathPrefix,
            'plugin_version' => '0.1.0',
        ])->assertCreated();

        return [BridgeInstallation::query()->where('uuid', $response->json('bridge_installation_id'))->firstOrFail(), $response->json('bridge_secret')];
    }

    /**
     * @return array<string, string>
     */
    private function hmacHeaders(BridgeInstallation $installation, string $secret, string $method, string $path, string $body = ''): array
    {
        $timestamp = (string) now()->timestamp;
        $nonce = 'test-nonce';
        $canonical = implode("\n", [
            $method,
            $path,
            $timestamp,
            $nonce,
            hash('sha256', $body),
        ]);

        return [
            'X-Core-Bridge-Id' => $installation->uuid,
            'X-Core-Timestamp' => $timestamp,
            'X-Core-Nonce' => $nonce,
            'X-Core-Signature' => hash_hmac('sha256', $canonical, $secret),
        ];
    }
}
