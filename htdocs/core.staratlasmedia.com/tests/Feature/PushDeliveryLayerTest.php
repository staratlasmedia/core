<?php

namespace Tests\Feature;

use App\Jobs\Push\SendPushBatchJob;
use App\Models\PushCampaign;
use App\Models\PushDeliveryLog;
use App\Models\PushGroup;
use App\Models\PushSubscription;
use App\Models\Site;
use App\Models\VapidKeySet;
use App\Services\Push\PushDeliveryService;
use App\Services\Push\PushPayloadBuilder;
use App\Services\Push\PushReportHandler;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Minishlink\WebPush\MessageSentReport;
use Tests\TestCase;

class PushDeliveryLayerTest extends TestCase
{
    use RefreshDatabase;

    public function test_payload_builder_normalizes_core_web_push_payload_shape(): void
    {
        [$site] = $this->siteAndVapid();

        $campaign = PushCampaign::query()->create([
            'site_id' => $site->id,
            'name' => 'Test campaign',
            'payload' => [
                'notification' => [
                    'title' => 'Title',
                    'body' => 'Body',
                    'url' => 'https://www.clubalfa.it/test/',
                    'tag' => 'test',
                ],
            ],
        ]);

        $payload = json_decode(app(PushPayloadBuilder::class)->build($campaign), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame(1, $payload['version']);
        $this->assertSame('cmp_'.$campaign->id, $payload['campaign_id']);
        $this->assertSame('clubalfa_it', $payload['site_code']);
        $this->assertSame('https://www.clubalfa.it/test/', $payload['notification']['url']);
        $this->assertArrayNotHasKey('data', $payload['notification']);
    }

    public function test_campaign_dispatch_queues_only_modern_dispatch_eligible_subscriptions(): void
    {
        Queue::fake();

        [$site, $vapid] = $this->siteAndVapid();

        $eligible = $this->subscription($site, $vapid, 'core_sdk', 'active', 'https://push.example/eligible');
        $this->subscription($site, $vapid, 'legacy_import', 'legacy_import_pending', 'https://push.example/pending');

        $campaign = PushCampaign::query()->create([
            'site_id' => $site->id,
            'name' => 'Dispatch test',
            'payload' => ['notification' => ['title' => 'Title', 'body' => 'Body', 'url' => '/']],
        ]);

        app(PushDeliveryService::class)->dispatchCampaign($campaign, batchSize: 50);

        Queue::assertPushed(
            SendPushBatchJob::class,
            fn (SendPushBatchJob $job): bool => $job->pushCampaignId === $campaign->id
                && $job->pushSubscriptionIds === [$eligible->id],
        );
    }

    public function test_report_handler_logs_success_and_invalidates_expired_subscriptions(): void
    {
        [$site, $vapid] = $this->siteAndVapid();
        $subscription = $this->subscription($site, $vapid, 'core_sdk', 'active', 'https://push.example/expired');
        $campaign = PushCampaign::query()->create([
            'site_id' => $site->id,
            'name' => 'Report test',
            'payload' => ['notification' => ['title' => 'Title', 'body' => 'Body', 'url' => '/']],
        ]);

        $report = new MessageSentReport(
            new Request('POST', 'https://push.example/expired'),
            new Response(410),
            false,
            'Gone',
        );

        app(PushReportHandler::class)->handle($campaign, $subscription, $report);

        $this->assertSame('invalid', $subscription->fresh()->status);
        $this->assertSame('expired', PushDeliveryLog::query()->firstOrFail()->status);
    }

    /**
     * @return array{Site, VapidKeySet}
     */
    private function siteAndVapid(): array
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
        $vapid = VapidKeySet::query()->create([
            'site_id' => $site->id,
            'name' => 'Test VAPID',
            'public_key' => 'public-key',
            'private_key_encrypted' => 'private-key',
            'source' => 'core',
            'active' => true,
        ]);

        return [$site, $vapid];
    }

    private function subscription(Site $site, VapidKeySet $vapid, string $source, string $status, string $endpoint): PushSubscription
    {
        return PushSubscription::query()->create([
            'site_id' => $site->id,
            'push_group_id' => $site->push_group_id,
            'source' => $source,
            'status' => $status,
            'origin' => $site->canonical_origin,
            'service_worker_url' => '/smart_sw.js',
            'service_worker_scope' => '/',
            'endpoint_hash' => hash('sha256', $endpoint),
            'endpoint_encrypted' => $endpoint,
            'p256dh_encrypted' => 'p256dh-secret',
            'auth_encrypted' => 'auth-secret',
            'vapid_key_set_id' => $vapid->id,
        ]);
    }
}
