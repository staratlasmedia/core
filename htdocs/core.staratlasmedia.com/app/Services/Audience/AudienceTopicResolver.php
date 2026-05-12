<?php

namespace App\Services\Audience;

use App\Models\AudienceTopic;
use Illuminate\Database\Eloquent\Collection;

class AudienceTopicResolver
{
    /**
     * @return Collection<int, AudienceTopic>
     */
    public function forChannel(?int $siteId, ?int $pushGroupId, string $channel, bool $visibleOnly = false): Collection
    {
        return AudienceTopic::query()
            ->where('status', 'active')
            ->where(function ($query) use ($siteId): void {
                $query->whereNull('site_id')->orWhere('site_id', $siteId);
            })
            ->where(function ($query) use ($pushGroupId): void {
                $query->whereNull('push_group_id')->orWhere('push_group_id', $pushGroupId);
            })
            ->whereHas('channelSettings', function ($query) use ($channel, $visibleOnly): void {
                $query->where('channel', $channel)->where('enabled', true);
                if ($visibleOnly) {
                    $query->where('visible_in_forms', true);
                }
            })
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();
    }
}
