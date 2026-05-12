<?php

namespace App\Services\Newsletter;

use App\Models\NewsletterSuppression;

class SuppressionService
{
    public function isSuppressed(string $emailHash, ?int $siteId = null, ?int $listId = null): bool
    {
        return NewsletterSuppression::query()
            ->where('email_hash', $emailHash)
            ->where(function ($query) use ($siteId, $listId): void {
                $query->where('scope', 'global')
                    ->orWhere(function ($siteQuery) use ($siteId): void {
                        $siteQuery->where('scope', 'site')->where('site_id', $siteId);
                    })
                    ->orWhere(function ($listQuery) use ($listId): void {
                        $listQuery->where('scope', 'list')->where('newsletter_list_id', $listId);
                    });
            })
            ->exists();
    }

    public function suppress(string $emailHash, string $reason, string $scope = 'global', ?int $siteId = null, ?int $listId = null, ?string $source = null, ?string $eventId = null): NewsletterSuppression
    {
        return NewsletterSuppression::query()->firstOrCreate(
            [
                'email_hash' => $emailHash,
                'scope' => $scope,
                'site_id' => $siteId,
                'newsletter_list_id' => $listId,
            ],
            [
                'reason' => $reason,
                'source' => $source,
                'event_id' => $eventId,
                'suppressed_at' => now(),
            ],
        );
    }
}
