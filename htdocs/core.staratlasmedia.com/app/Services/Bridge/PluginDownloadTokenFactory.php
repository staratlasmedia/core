<?php

namespace App\Services\Bridge;

use App\Models\BridgeInstallation;
use App\Models\PluginRelease;
use App\Models\PluginUpdateDownload;
use Illuminate\Support\Str;

class PluginDownloadTokenFactory
{
    /**
     * @return array{token: string, record: PluginUpdateDownload}
     */
    public function issue(PluginRelease $release, ?BridgeInstallation $installation = null): array
    {
        $token = 'sacbd_'.Str::random(48);

        $record = PluginUpdateDownload::query()->create([
            'plugin_release_id' => $release->id,
            'bridge_installation_id' => $installation?->id,
            'site_id' => $installation?->site_id,
            'download_token_hash' => $this->hash($token),
            'status' => 'issued',
            'expires_at' => now()->addMinutes((int) config('core.bridge.plugin_download_ttl_minutes', 30)),
        ]);

        return ['token' => $token, 'record' => $record];
    }

    public function hash(string $token): string
    {
        return hash('sha256', $token);
    }
}
