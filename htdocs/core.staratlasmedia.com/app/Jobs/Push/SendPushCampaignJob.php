<?php

namespace App\Jobs\Push;

use App\Models\PushCampaign;
use App\Services\Push\PushDeliveryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendPushCampaignJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @var array<int>
     */
    public array $backoff = [60, 300, 900];

    public function __construct(public int $pushCampaignId) {}

    public function handle(PushDeliveryService $deliveryService): void
    {
        $campaign = PushCampaign::query()->findOrFail($this->pushCampaignId);

        $deliveryService->dispatchCampaign($campaign);
    }
}
