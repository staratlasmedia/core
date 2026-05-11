<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class BridgeInstallation extends CoreModel
{
    use SoftDeletes;

    protected $hidden = [
        'bridge_secret_encrypted',
    ];

    protected function casts(): array
    {
        return [
            'bridge_secret_encrypted' => 'encrypted',
            'last_seen_at' => 'datetime',
            'last_config_sync_at' => 'datetime',
            'metadata_json' => 'array',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function pushGroup(): BelongsTo
    {
        return $this->belongsTo(PushGroup::class);
    }

    public function siteOrigin(): BelongsTo
    {
        return $this->belongsTo(SiteOrigin::class);
    }

    public function setupToken(): BelongsTo
    {
        return $this->belongsTo(BridgeSetupToken::class, 'setup_token_id');
    }

    public function configVersions(): HasMany
    {
        return $this->hasMany(BridgeConfigVersion::class);
    }

    public function activeConfigVersion(): HasOne
    {
        return $this->hasOne(BridgeConfigVersion::class)->where('active', true)->latestOfMany();
    }

    public function pluginUpdateDownloads(): HasMany
    {
        return $this->hasMany(PluginUpdateDownload::class);
    }
}
