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

    public function authProviderSiteSettings(): HasMany
    {
        return $this->hasMany(AuthProviderSiteSetting::class);
    }

    public function authAuthorizationCodes(): HasMany
    {
        return $this->hasMany(AuthAuthorizationCode::class);
    }

    public function magicLinkTokens(): HasMany
    {
        return $this->hasMany(MagicLinkToken::class);
    }

    public function loginEvents(): HasMany
    {
        return $this->hasMany(LoginEvent::class);
    }

    public function commentThreads(): HasMany
    {
        return $this->hasMany(CommentThread::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function commentSettings(): HasMany
    {
        return $this->hasMany(CommentSetting::class);
    }
}
