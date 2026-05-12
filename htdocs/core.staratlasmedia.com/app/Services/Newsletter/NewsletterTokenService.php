<?php

namespace App\Services\Newsletter;

use App\Models\NewsletterToken;
use Illuminate\Support\Str;

class NewsletterTokenService
{
    public function issue(string $type, ?int $subscriberId = null, ?int $campaignId = null, ?int $deliveryLogId = null, ?int $listId = null, int $ttlMinutes = 10080, array $metadata = []): string
    {
        $raw = Str::random(64);

        NewsletterToken::query()->create([
            'newsletter_subscriber_id' => $subscriberId,
            'newsletter_campaign_id' => $campaignId,
            'newsletter_delivery_log_id' => $deliveryLogId,
            'newsletter_list_id' => $listId,
            'type' => $type,
            'token_hash' => hash('sha256', $raw),
            'status' => 'active',
            'expires_at' => now()->addMinutes($ttlMinutes),
            'metadata_json' => $metadata,
        ]);

        return $raw;
    }
}
