<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PluginUpdateDownload extends CoreModel
{
    protected $hidden = [
        'download_token_hash',
        'ip_hash',
        'user_agent_hash',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'downloaded_at' => 'datetime',
            'metadata_json' => 'array',
        ];
    }

    public function pluginRelease(): BelongsTo
    {
        return $this->belongsTo(PluginRelease::class);
    }

    public function bridgeInstallation(): BelongsTo
    {
        return $this->belongsTo(BridgeInstallation::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
