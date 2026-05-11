<?php

namespace App\Services\Push;

use App\Jobs\Push\SendPushBatchJob;
use App\Models\PushCampaign;
use App\Models\PushSubscription;
use Minishlink\WebPush\Subscription;

class PushDeliveryService
{
    public function __construct(
        private readonly WebPushClientFactory $clientFactory,
        private readonly PushPayloadBuilder $payloadBuilder,
        private readonly PushReportHandler $reportHandler,
    ) {}

    public function dispatchCampaign(PushCampaign $campaign, int $batchSize = 500): void
    {
        PushSubscription::query()
            ->modernDispatchEligible()
            ->when($campaign->site_id !== null, fn ($query) => $query->where('site_id', $campaign->site_id))
            ->select('id')
            ->orderBy('id')
            ->chunkById($batchSize, function ($subscriptions) use ($campaign): void {
                SendPushBatchJob::dispatch(
                    $campaign->id,
                    $subscriptions->pluck('id')->all(),
                )->onQueue('push');
            });
    }

    /**
     * @param  array<int>  $subscriptionIds
     */
    public function sendBatch(PushCampaign $campaign, array $subscriptionIds): int
    {
        $subscriptions = PushSubscription::query()
            ->with('vapidKeySet')
            ->modernDispatchEligible()
            ->whereKey($subscriptionIds)
            ->get();

        $payload = $this->payloadBuilder->build($campaign->loadMissing('site'));
        $sent = 0;

        foreach ($subscriptions->groupBy('vapid_key_set_id') as $group) {
            $vapidKeySet = $group->first()?->vapidKeySet;

            if ($vapidKeySet === null) {
                continue;
            }

            $webPush = $this->clientFactory->make($vapidKeySet);
            $byEndpoint = [];

            foreach ($group as $subscription) {
                $endpoint = $subscription->endpoint_encrypted;

                if (! is_string($endpoint) || $endpoint === '') {
                    continue;
                }

                $webPush->queueNotification(
                    Subscription::create([
                        'endpoint' => $endpoint,
                        'keys' => [
                            'p256dh' => $subscription->p256dh_encrypted,
                            'auth' => $subscription->auth_encrypted,
                        ],
                    ]),
                    $payload,
                );

                $byEndpoint[$endpoint] = $subscription;
            }

            foreach ($webPush->flush(config('core.web_push.batch_size')) as $report) {
                $subscription = $byEndpoint[$report->getEndpoint()] ?? null;

                if ($subscription === null) {
                    continue;
                }

                $this->reportHandler->handle($campaign, $subscription, $report);
                $sent++;
            }
        }

        return $sent;
    }
}
