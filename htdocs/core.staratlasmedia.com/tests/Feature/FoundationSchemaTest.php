<?php

namespace Tests\Feature;

use App\Filament\Resources\PushSubscriptionResource;
use App\Filament\Resources\VapidKeySetResource;
use App\Models\Comment;
use App\Models\PublisherProvidedId;
use App\Models\PushGroup;
use App\Models\PushSubscriber;
use App\Models\PushSubscription;
use App\Models\PushTopic;
use App\Models\Site;
use App\Models\User;
use App\Models\VapidKeySet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FoundationSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_foundation_tables_and_critical_columns_exist(): void
    {
        $tables = [
            'sites',
            'site_origins',
            'allowed_origins',
            'api_clients',
            'sdk_tokens',
            'push_groups',
            'social_identities',
            'publisher_provided_ids',
            'auth_authorization_codes',
            'auth_sessions',
            'login_events',
            'legacy_push_apps',
            'vapid_key_sets',
            'push_subscribers',
            'push_subscriptions',
            'push_subscription_contexts',
            'push_reconfirmation_events',
            'push_topics',
            'push_subscription_topics',
            'push_campaigns',
            'push_campaign_targets',
            'push_delivery_logs',
            'comments',
            'comment_reactions',
            'comment_reports',
            'comment_moderation_events',
            'newsletter_subscribers',
            'newsletter_lists',
            'newsletter_list_subscriber',
            'newsletter_events',
            'ses_webhook_events',
            'audit_logs',
            'webhook_events',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table [{$table}].");
        }

        foreach (['uuid', 'status', 'metadata', 'deleted_at'] as $column) {
            $this->assertTrue(Schema::hasColumn('users', $column), "Missing users.{$column}.");
        }

        foreach (['endpoint_hash', 'endpoint_encrypted', 'p256dh_encrypted', 'auth_encrypted', 'vapid_key_set_id'] as $column) {
            $this->assertTrue(Schema::hasColumn('push_subscriptions', $column), "Missing push_subscriptions.{$column}.");
        }

        foreach (['sites', 'legacy_push_apps', 'push_subscriptions'] as $table) {
            $this->assertTrue(Schema::hasColumn($table, 'push_group_id'), "Missing {$table}.push_group_id.");
        }

        $this->assertTrue(Schema::hasColumn('vapid_key_sets', 'private_key_encrypted'));
        $this->assertFalse(Schema::hasColumn('users', 'ppid'));
    }

    public function test_phase_four_push_groups_are_seeded_with_clubalfa_manifest_defaults(): void
    {
        $clubAlfaIt = PushGroup::query()->where('code', 'clubalfa_it')->firstOrFail();
        $clubAlfaEn = PushGroup::query()->where('code', 'clubalfa_en')->firstOrFail();

        $this->assertSame('/pwa/clubalfa-it', $clubAlfaIt->manifest_id);
        $this->assertSame('/', $clubAlfaIt->manifest_scope);
        $this->assertSame('/pwa-start/?app=clubalfa_it', $clubAlfaIt->manifest_start_url);
        $this->assertSame('/smart_sw.js', $clubAlfaIt->service_worker_url);

        $this->assertSame('/pwa/clubalfa-en', $clubAlfaEn->manifest_id);
        $this->assertSame('/en/', $clubAlfaEn->manifest_scope);
        $this->assertSame('/en/pwa-start/?app=clubalfa_en', $clubAlfaEn->manifest_start_url);
        $this->assertSame('/en/smart_sw.js', $clubAlfaEn->service_worker_url);
    }

    public function test_core_model_relationships_are_wired(): void
    {
        $site = Site::create([
            'code' => 'clubalfa_it',
            'name' => 'ClubAlfa IT',
            'canonical_origin' => 'https://www.clubalfa.it',
            'language' => 'it',
            'push_group' => 'clubalfa_it',
            'push_group_id' => PushGroup::query()->where('code', 'clubalfa_it')->value('id'),
        ]);

        $origin = $site->origins()->create([
            'origin' => 'https://www.clubalfa.it',
            'path_prefix' => '/',
            'is_primary' => true,
        ]);

        $user = User::create([
            'name' => 'Core Tester',
            'email' => 'tester@example.test',
            'password' => 'password',
        ]);

        PublisherProvidedId::create([
            'user_id' => $user->id,
            'site_id' => $site->id,
            'ppid' => 'ppid_test',
            'scope' => 'site',
        ]);

        $parent = Comment::create([
            'site_id' => $site->id,
            'site_origin_id' => $origin->id,
            'user_id' => $user->id,
            'external_post_url_hash' => hash('sha256', 'https://www.clubalfa.it/post/'),
            'source_url' => 'https://www.clubalfa.it/post/',
            'body' => 'Parent comment',
            'status' => 'approved',
        ]);

        $reply = Comment::create([
            'site_id' => $site->id,
            'parent_id' => $parent->id,
            'external_post_url_hash' => hash('sha256', 'https://www.clubalfa.it/post/'),
            'body' => 'Reply comment',
            'status' => 'approved',
        ]);

        $this->assertSame($site->id, $origin->site->id);
        $this->assertSame('clubalfa_it', $site->pushGroup->code);
        $this->assertSame('ppid_test', $user->publisherProvidedIds()->first()->ppid);
        $this->assertSame($parent->id, $reply->parent->id);
        $this->assertSame($reply->id, $parent->replies()->first()->id);
    }

    public function test_sensitive_push_and_token_fields_are_encrypted_or_hidden(): void
    {
        $site = Site::create([
            'code' => 'motorisumotori_it',
            'name' => 'MotoriSuMotori',
            'canonical_origin' => 'https://www.motorisumotori.it',
            'language' => 'it',
            'push_group' => 'motorisumotori_it',
        ]);

        $vapid = VapidKeySet::create([
            'site_id' => $site->id,
            'name' => 'Test VAPID',
            'public_key' => 'public-key',
            'private_key_encrypted' => 'private-key-secret',
            'source' => 'core',
            'active' => true,
        ]);

        $subscriber = PushSubscriber::create([
            'site_id' => $site->id,
            'language' => 'it',
        ]);

        $subscription = PushSubscription::create([
            'site_id' => $site->id,
            'push_subscriber_id' => $subscriber->id,
            'source' => 'core_sdk',
            'status' => 'active',
            'origin' => 'https://www.motorisumotori.it',
            'service_worker_url' => '/smart_sw.js',
            'service_worker_scope' => '/',
            'endpoint_hash' => hash('sha256', 'https://push.example/subscription'),
            'endpoint_encrypted' => 'https://push.example/subscription',
            'p256dh_encrypted' => 'p256dh-secret',
            'auth_encrypted' => 'auth-secret',
            'vapid_key_set_id' => $vapid->id,
        ]);

        $topic = PushTopic::create([
            'site_id' => $site->id,
            'type' => 'section',
            'slug' => 'news',
            'label' => 'News',
        ]);

        $subscription->topics()->attach($topic);

        $rawVapid = DB::table('vapid_key_sets')->whereKey($vapid->id)->value('private_key_encrypted');
        $rawEndpoint = DB::table('push_subscriptions')->whereKey($subscription->id)->value('endpoint_encrypted');

        $this->assertNotSame('private-key-secret', $rawVapid);
        $this->assertNotSame('https://push.example/subscription', $rawEndpoint);
        $this->assertSame('private-key-secret', $vapid->fresh()->private_key_encrypted);
        $this->assertSame('https://push.example/subscription', $subscription->fresh()->endpoint_encrypted);
        $this->assertArrayNotHasKey('private_key_encrypted', $vapid->toArray());
        $this->assertArrayNotHasKey('endpoint_encrypted', $subscription->toArray());
        $this->assertSame($topic->id, $subscription->topics()->first()->id);
    }

    public function test_filament_resources_do_not_expose_private_keys_and_keep_push_subscriptions_read_only(): void
    {
        $this->assertFalse(VapidKeySetResource::canCreate());
        $this->assertFalse(VapidKeySetResource::canEdit(new VapidKeySet));
        $this->assertFalse(VapidKeySetResource::canDelete(new VapidKeySet));
        $this->assertFalse(PushSubscriptionResource::canCreate());
        $this->assertFalse(PushSubscriptionResource::canEdit(new PushSubscription));
        $this->assertFalse(PushSubscriptionResource::canDelete(new PushSubscription));

        $resourceSource = file_get_contents(app_path('Filament/Resources/VapidKeySetResource.php'));

        $this->assertIsString($resourceSource);
        $this->assertStringNotContainsString('private_key_encrypted', $resourceSource);
        $this->assertInstanceOf(Model::class, new PushSubscription);
    }
}
