<?php

namespace App\Services\Push;

use App\Models\PushCampaign;
use App\Models\PushDeliveryLog;
use App\Models\PushSubscription;
use Illuminate\Support\Carbon;
use Minishlink\WebPush\MessageSentReport;

class PushReportHandler
{
    public function handle(PushCampaign $campaign, PushSubscription $subscription, MessageSentReport $report): void
    {
        $responseCode = $report->getResponse()?->getStatusCode();
        $now = Carbon::now();
        $status = $this->statusFor($report, $responseCode);

        PushDeliveryLog::query()->create([
            'push_campaign_id' => $campaign->id,
            'push_subscription_id' => $subscription->id,
            'site_id' => $subscription->site_id,
            'status' => $status,
            'response_code' => $responseCode,
            'error' => $report->isSuccess() ? null : mb_substr($report->getReason(), 0, 1000),
            'attempted_at' => $now,
            'delivered_at' => $report->isSuccess() ? $now : null,
            'metadata' => [
                'expired' => $report->isSubscriptionExpired(),
                'retryable' => $this->isRetryable($responseCode),
            ],
        ]);

        if ($report->isSubscriptionExpired()) {
            $subscription->forceFill(['status' => 'invalid'])->save();
        }
    }

    private function statusFor(MessageSentReport $report, ?int $responseCode): string
    {
        if ($report->isSuccess()) {
            return 'sent';
        }

        if ($report->isSubscriptionExpired()) {
            return 'expired';
        }

        if ($this->isRetryable($responseCode)) {
            return 'retryable';
        }

        return 'failed';
    }

    private function isRetryable(?int $responseCode): bool
    {
        return $responseCode === 429 || ($responseCode !== null && $responseCode >= 500);
    }
}
