<?php

namespace App\Services\Push;

use App\Models\PushCampaign;

class PushPayloadBuilder
{
    public function build(PushCampaign $campaign): string
    {
        $payload = $campaign->payload ?? [];
        $notification = $payload['notification'] ?? $payload;

        $siteCode = $campaign->site?->code ?? $payload['site_code'] ?? null;

        $normalized = [
            'version' => 1,
            'campaign_id' => $payload['campaign_id'] ?? 'cmp_'.$campaign->getKey(),
            'site_code' => $siteCode,
            'notification' => [
                'title' => $notification['title'] ?? '',
                'body' => $notification['body'] ?? '',
                'url' => $notification['url'] ?? '/',
                'icon' => $notification['icon'] ?? null,
                'badge' => $notification['badge'] ?? null,
                'image' => $notification['image'] ?? null,
                'tag' => $notification['tag'] ?? null,
            ],
        ];

        return json_encode($normalized, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
