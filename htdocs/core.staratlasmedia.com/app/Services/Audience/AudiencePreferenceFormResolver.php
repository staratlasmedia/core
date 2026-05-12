<?php

namespace App\Services\Audience;

use App\Models\AudiencePreferenceForm;

class AudiencePreferenceFormResolver
{
    public function resolve(?int $siteId, ?int $pushGroupId, ?int $bridgeInstallationId, string $channel): ?AudiencePreferenceForm
    {
        return AudiencePreferenceForm::query()
            ->where('channel', $channel)
            ->where('status', 'active')
            ->where(function ($query) use ($siteId, $pushGroupId, $bridgeInstallationId): void {
                $query->where('bridge_installation_id', $bridgeInstallationId)
                    ->orWhere('push_group_id', $pushGroupId)
                    ->orWhere('site_id', $siteId)
                    ->orWhereNull('site_id');
            })
            ->orderByRaw('bridge_installation_id is null')
            ->orderByRaw('push_group_id is null')
            ->orderByRaw('site_id is null')
            ->first();
    }
}
