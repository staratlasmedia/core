<?php

namespace Tests\Feature;

use App\Filament\Pages\NewsletterDashboard;
use App\Filament\Resources\AiProviderResource;
use App\Filament\Resources\AudienceTopicResource;
use App\Filament\Resources\EmailSenderIdentityResource;
use App\Filament\Resources\NewsletterImportBatchResource;
use App\Filament\Resources\NewsletterSettingResource;
use App\Models\AiProvider;
use App\Models\AudienceTopic;
use App\Models\AudienceTopicChannelSetting;
use App\Models\EditorialContentItem;
use App\Models\EditorialContentSource;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterDigestRecipe;
use App\Models\NewsletterImportBatch;
use App\Models\NewsletterList;
use App\Models\NewsletterSetting;
use App\Models\NewsletterSubscriber;
use App\Models\NewsletterSuppression;
use App\Models\NewsletterToken;
use App\Models\SnsWebhookEvent;
use App\Models\PushGroup;
use App\Models\Site;
use App\Services\Ai\AiGenerationService;
use App\Services\Audience\AudienceTopicResolver;
use App\Services\Editorial\EditorialContentIngestService;
use App\Services\Newsletter\NewsletterCsvImportService;
use App\Services\Newsletter\NewsletterDigestService;
use App\Services\Newsletter\SnsMessageVerifier;
use App\Services\Newsletter\NewsletterSettingsResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class NewsletterPhase9Test extends TestCase
{
    use RefreshDatabase;

    public function test_phase_nine_schema_exists_and_keeps_push_compatibility(): void
    {
        foreach ([
            'newsletter_settings',
            'email_sender_identities',
            'audience_topics',
            'audience_topic_channel_settings',
            'newsletter_subscriber_topic_preferences',
            'push_subscription_topic_preferences',
            'audience_topic_mappings',
            'newsletter_import_batches',
            'newsletter_import_rows',
            'editorial_content_sources',
            'editorial_content_items',
            'newsletter_digest_recipes',
            'newsletter_campaigns',
            'newsletter_delivery_logs',
            'newsletter_tokens',
            'newsletter_engagement_events',
            'ai_providers',
            'ai_model_profiles',
            'ai_prompt_templates',
            'ai_generation_jobs',
            'sns_webhook_events',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table [{$table}].");
        }

        $this->assertTrue(Schema::hasTable('push_topics'));
        $this->assertTrue(Schema::hasTable('push_subscription_topics'));
        $this->assertTrue(Schema::hasColumn('newsletter_subscribers', 'normalized_email_hash'));
        $this->assertTrue(Schema::hasColumn('newsletter_subscribers', 'bridge_installation_id'));
        $this->assertTrue(Schema::hasColumn('newsletter_import_batches', 'committed_at'));
        $this->assertTrue(Schema::hasColumn('sns_webhook_events', 'payload_hash'));
    }

    public function test_newsletter_settings_resolver_defaults_disabled_and_prefers_installation_scope(): void
    {
        [$site, $pushGroup] = $this->site();
        $resolver = app(NewsletterSettingsResolver::class);

        $this->assertFalse($resolver->resolve($site->id, $pushGroup->id)->newsletterEnabled);

        NewsletterSetting::query()->create([
            'scope' => NewsletterSetting::SCOPE_SITE,
            'scope_key' => NewsletterSetting::scopeKey(NewsletterSetting::SCOPE_SITE, $site->id),
            'site_id' => $site->id,
            'newsletter_enabled' => true,
            'send_enabled' => false,
        ]);

        $resolved = $resolver->resolve($site->id, $pushGroup->id);

        $this->assertTrue($resolved->newsletterEnabled);
        $this->assertFalse($resolved->sendEnabled);
        $this->assertTrue($resolved->doubleOptIn);
    }

    public function test_audience_topics_are_canonical_and_channel_filtered(): void
    {
        [$site, $pushGroup] = $this->site();
        $topic = AudienceTopic::query()->create([
            'site_id' => $site->id,
            'push_group_id' => $pushGroup->id,
            'type' => 'manual',
            'slug' => 'phase-nine',
            'label' => 'Phase Nine',
            'status' => 'active',
        ]);
        AudienceTopicChannelSetting::query()->create([
            'audience_topic_id' => $topic->id,
            'channel' => 'newsletter',
            'enabled' => true,
            'visible_in_forms' => true,
        ]);

        $topics = app(AudienceTopicResolver::class)->forChannel($site->id, $pushGroup->id, 'newsletter', true);

        $this->assertSame($topic->id, $topics->first()->id);
    }

    public function test_csv_import_dry_run_commit_detects_duplicates_and_suppressions_without_sending(): void
    {
        [$site, $pushGroup] = $this->site();
        $this->enableNewsletter($site, allowImport: true);
        $list = NewsletterList::query()->create(['site_id' => $site->id, 'push_group_id' => $pushGroup->id, 'code' => 'daily', 'slug' => 'daily', 'name' => 'Daily']);
        NewsletterSuppression::query()->create([
            'email_hash' => hash('sha256', 'blocked@example.test'),
            'reason' => 'manual',
            'scope' => 'global',
            'suppressed_at' => now(),
        ]);
        $batch = NewsletterImportBatch::query()->create(['site_id' => $site->id, 'push_group_id' => $pushGroup->id, 'newsletter_list_id' => $list->id]);
        $csv = "email,language\nreader@example.test,it\nreader@example.test,it\nblocked@example.test,it\ninvalid,it\n";

        $service = app(NewsletterCsvImportService::class);
        $report = $service->dryRun($batch, $csv, ['email' => 'email', 'language' => 'language'], ['respect_suppression' => true]);
        $stats = $service->commit($batch->fresh());

        $this->assertSame(1, $report['valid_rows']);
        $this->assertSame(1, $report['duplicate_rows']);
        $this->assertSame(1, $report['suppressed_rows']);
        $this->assertSame(1, $report['invalid_rows']);
        $this->assertSame(1, $stats['imported_rows']);
        $this->assertNotNull($batch->fresh()->committed_at);
        $this->assertSame(1, NewsletterSubscriber::query()->where('status', 'subscribed')->count());
    }

    public function test_ai_provider_returns_marked_mock_when_disabled_or_unconfigured(): void
    {
        $provider = AiProvider::query()->create([
            'code' => 'openai-placeholder',
            'name' => 'OpenAI Placeholder',
            'provider_type' => 'openai',
            'status' => 'disabled',
        ]);

        $result = app(AiGenerationService::class)->testProvider($provider);

        $this->assertSame('mock', $result['status']);
        $this->assertSame('mock', $provider->fresh()->last_test_result_json['status']);
    }

    public function test_rss_manual_test_fetch_is_bounded_and_stores_items(): void
    {
        [$site, $pushGroup] = $this->site();
        $this->enableNewsletter($site, rss: true);
        Http::fake([
            'https://example.test/feed.xml' => Http::response('<?xml version="1.0"?><rss><channel><item><title>One</title><link>https://example.test/one</link><description>Excerpt</description><pubDate>Tue, 12 May 2026 10:00:00 GMT</pubDate></item></channel></rss>'),
        ]);

        $source = EditorialContentSource::query()->create([
            'site_id' => $site->id,
            'push_group_id' => $pushGroup->id,
            'type' => 'rss',
            'code' => 'test-rss',
            'name' => 'Test RSS',
            'url' => 'https://example.test/feed.xml',
            'status' => 'active',
        ]);

        $preview = app(EditorialContentIngestService::class)->testFetch($source, 5);
        $result = app(EditorialContentIngestService::class)->testFetch($source, 5, true);

        $this->assertFalse((bool) ($preview['persisted'] ?? false));
        $this->assertSame('ok', $result['status']);
        $this->assertCount(1, $result['items']);
        $this->assertSame(1, EditorialContentItem::query()->count());
    }

    public function test_digest_recipe_creates_editorial_review_draft_without_auto_send(): void
    {
        [$site, $pushGroup] = $this->site();
        $this->enableNewsletter($site);
        $list = NewsletterList::query()->create(['site_id' => $site->id, 'push_group_id' => $pushGroup->id, 'code' => 'weekly', 'slug' => 'weekly', 'name' => 'Weekly']);
        EditorialContentItem::query()->create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'site_id' => $site->id,
            'push_group_id' => $pushGroup->id,
            'source_type' => 'manual',
            'source_url' => 'https://example.test/article',
            'source_url_hash' => hash('sha256', 'https://example.test/article'),
            'title' => 'Article',
            'published_at' => now(),
        ]);
        $recipe = NewsletterDigestRecipe::query()->create([
            'site_id' => $site->id,
            'push_group_id' => $pushGroup->id,
            'newsletter_list_id' => $list->id,
            'name' => 'Weekly Digest',
            'code' => 'weekly-digest',
            'status' => 'active',
            'frequency' => 'weekly',
            'auto_send' => false,
            'auto_schedule' => false,
            'require_editorial_approval' => true,
        ]);

        $run = app(NewsletterDigestService::class)->createDraftRun($recipe);

        $this->assertSame('editorial_review', $run->status);
        $this->assertFalse((bool) $recipe->auto_send);
        $this->assertNotNull($run->newsletter_campaign_id);
    }

    public function test_digest_generation_refreshes_enabled_sources_before_creating_draft(): void
    {
        [$site, $pushGroup] = $this->site();
        $this->enableNewsletter($site, rss: true);
        $list = NewsletterList::query()->create(['site_id' => $site->id, 'push_group_id' => $pushGroup->id, 'code' => 'daily', 'slug' => 'daily', 'name' => 'Daily']);
        Http::fake([
            'https://example.test/digest.xml' => Http::response('<?xml version="1.0"?><rss><channel><item><title>Fresh Digest Article</title><link>https://example.test/fresh</link><description>Fresh excerpt</description><pubDate>Tue, 12 May 2026 10:00:00 GMT</pubDate></item></channel></rss>'),
        ]);

        $source = EditorialContentSource::query()->create([
            'site_id' => $site->id,
            'push_group_id' => $pushGroup->id,
            'type' => 'rss',
            'code' => 'digest-rss',
            'name' => 'Digest RSS',
            'url' => 'https://example.test/digest.xml',
            'status' => 'active',
        ]);
        $recipe = NewsletterDigestRecipe::query()->create([
            'site_id' => $site->id,
            'push_group_id' => $pushGroup->id,
            'newsletter_list_id' => $list->id,
            'name' => 'Daily Digest',
            'code' => 'daily-digest',
            'status' => 'active',
            'frequency' => 'daily',
            'auto_send' => false,
            'auto_schedule' => false,
            'require_editorial_approval' => true,
            'max_items' => 5,
        ]);
        DB::table('newsletter_digest_recipe_sources')->insert([
            'newsletter_digest_recipe_id' => $recipe->id,
            'editorial_content_source_id' => $source->id,
            'enabled' => true,
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $run = app(NewsletterDigestService::class)->createDraftRun($recipe);

        $this->assertDatabaseHas('editorial_content_items', ['title' => 'Fresh Digest Article']);
        $this->assertDatabaseHas('newsletter_campaigns', [
            'id' => $run->newsletter_campaign_id,
            'status' => 'editorial_review',
        ]);
        $this->assertSame('digest-rss', $run->fresh()->metadata_json['content_source_fetches'][0]['code']);
        $this->assertStringContainsString('Fresh Digest Article', NewsletterCampaign::query()->findOrFail($run->newsletter_campaign_id)->html_body);
    }

    public function test_tracking_endpoint_records_open_without_storing_raw_token(): void
    {
        $raw = 'raw-test-token';
        NewsletterToken::query()->create([
            'type' => 'open',
            'token_hash' => hash('sha256', $raw),
            'status' => 'active',
            'expires_at' => now()->addHour(),
        ]);

        $this->get('/newsletter/o/'.$raw.'.gif')->assertOk();

        $this->assertDatabaseHas('newsletter_engagement_events', ['event_type' => 'open']);
        $this->assertDatabaseMissing('newsletter_tokens', ['token_hash' => $raw]);
    }

    public function test_phase_nine_filament_surfaces_exist(): void
    {
        $this->assertTrue(class_exists(NewsletterDashboard::class));
        $this->assertTrue(NewsletterSettingResource::canCreate());
        $this->assertTrue(EmailSenderIdentityResource::canCreate());
        $this->assertTrue(AiProviderResource::canCreate());
        $this->assertTrue(NewsletterImportBatchResource::canCreate());
        $this->assertTrue(AudienceTopicResource::canCreate());
    }

    public function test_public_subscribe_requires_consent_when_settings_require_it_and_filters_topics(): void
    {
        [$site, $pushGroup] = $this->site();
        $this->enableNewsletter($site);
        $topic = AudienceTopic::query()->create([
            'site_id' => $site->id,
            'push_group_id' => $pushGroup->id,
            'type' => 'manual',
            'slug' => 'allowed',
            'label' => 'Allowed',
            'status' => 'active',
        ]);
        AudienceTopicChannelSetting::query()->create([
            'audience_topic_id' => $topic->id,
            'channel' => 'newsletter',
            'enabled' => true,
            'visible_in_forms' => true,
        ]);

        $this->postJson('/api/v1/newsletter/subscribe', [
            'site_code' => $site->code,
            'email' => 'reader@example.test',
        ])->assertOk()->assertJson(['status' => 'consent_required']);

        $this->postJson('/api/v1/newsletter/subscribe', [
            'site_code' => $site->code,
            'email' => 'reader@example.test',
            'consent_version' => 'v1',
            'topic_ids' => [$topic->id, 999999],
        ])->assertOk()->assertJson(['status' => 'pending']);

        $this->assertDatabaseHas('newsletter_subscriber_topic_preferences', ['audience_topic_id' => $topic->id]);
        $this->assertDatabaseMissing('newsletter_subscriber_topic_preferences', ['audience_topic_id' => 999999]);
    }

    public function test_unsubscribe_requires_valid_token_not_subscriber_uuid_only(): void
    {
        [$site] = $this->site();
        $subscriber = NewsletterSubscriber::query()->create([
            'site_id' => $site->id,
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'email_hash' => hash('sha256', 'reader@example.test'),
            'normalized_email_hash' => hash('sha256', 'reader@example.test'),
            'email_encrypted' => 'reader@example.test',
            'status' => 'subscribed',
        ]);

        $this->postJson('/api/v1/newsletter/unsubscribe', ['subscriber_uuid' => $subscriber->uuid])->assertUnprocessable();

        $raw = 'unsubscribe-token';
        NewsletterToken::query()->create([
            'newsletter_subscriber_id' => $subscriber->id,
            'type' => 'unsubscribe',
            'token_hash' => hash('sha256', $raw),
            'status' => 'active',
            'expires_at' => now()->addHour(),
        ]);

        $this->postJson('/api/v1/newsletter/unsubscribe', ['token' => $raw])->assertOk()->assertJson(['status' => 'unsubscribed']);
    }

    public function test_ses_controlled_test_is_blocked_by_global_kill_switch(): void
    {
        Mail::fake();
        $identity = \App\Models\EmailSenderIdentity::query()->create([
            'code' => 'test',
            'from_name' => 'Core',
            'from_email' => 'noreply@example.test',
            'send_enabled' => true,
            'test_send_enabled' => true,
            'status' => 'active',
        ]);

        $result = app(\App\Services\Newsletter\NewsletterSesTestService::class)->sendControlledTest($identity, 'reader@example.test');

        $this->assertSame('blocked', $result['status']);
        $this->assertSame('global_newsletter_send_disabled', $result['reason']);
        Mail::assertNothingSent();
    }

    public function test_sns_subscription_confirmation_is_verified_and_not_auto_confirmed(): void
    {
        $this->instance(SnsMessageVerifier::class, new class extends SnsMessageVerifier {
            public function verify(array $message, ?string $expectedTopicArn = null): bool
            {
                return true;
            }
        });

        $this->postJson('/api/webhooks/aws/sns/ses', [
            'Type' => 'SubscriptionConfirmation',
            'MessageId' => 'sns-confirm-1',
            'TopicArn' => 'arn:aws:sns:eu-west-1:123:ses',
            'SubscribeURL' => 'https://sns.eu-west-1.amazonaws.com/confirm',
        ])->assertOk();

        $event = SnsWebhookEvent::query()->where('sns_message_id', 'sns-confirm-1')->firstOrFail();

        $this->assertSame('processed', $event->status);
        $this->assertFalse((bool) $event->metadata_json['auto_confirmed']);
        $this->assertNotNull($event->verified_at);
    }

    /**
     * @return array{Site, PushGroup}
     */
    private function site(): array
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

        return [$site, $pushGroup];
    }

    private function enableNewsletter(Site $site, bool $allowImport = false, bool $rss = false, bool $wordpress = false, bool $ai = false, bool $send = false): void
    {
        NewsletterSetting::query()->updateOrCreate(
            [
                'scope_key' => NewsletterSetting::scopeKey(NewsletterSetting::SCOPE_SITE, $site->id),
            ],
            [
                'scope' => NewsletterSetting::SCOPE_SITE,
                'site_id' => $site->id,
                'newsletter_enabled' => true,
                'double_opt_in' => true,
                'require_consent' => true,
                'send_enabled' => $send,
                'allow_import' => $allowImport,
                'ai_generation_enabled' => $ai,
                'rss_import_enabled' => $rss,
                'wordpress_api_import_enabled' => $wordpress,
                'automatic_digest_enabled' => false,
            ],
        );
    }
}
