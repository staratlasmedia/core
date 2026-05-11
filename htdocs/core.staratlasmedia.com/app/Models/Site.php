<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends CoreModel
{
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function origins(): HasMany
    {
        return $this->hasMany(SiteOrigin::class);
    }

    public function pushGroup(): BelongsTo
    {
        return $this->belongsTo(PushGroup::class);
    }

    public function allowedOrigins(): HasMany
    {
        return $this->hasMany(AllowedOrigin::class);
    }

    public function apiClients(): HasMany
    {
        return $this->hasMany(ApiClient::class);
    }

    public function sdkTokens(): HasMany
    {
        return $this->hasMany(SdkToken::class);
    }

    public function publisherProvidedIds(): HasMany
    {
        return $this->hasMany(PublisherProvidedId::class);
    }

    public function legacyPushApps(): HasMany
    {
        return $this->hasMany(LegacyPushApp::class);
    }

    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class);
    }

    public function bridgeSetupTokens(): HasMany
    {
        return $this->hasMany(BridgeSetupToken::class);
    }

    public function bridgeInstallations(): HasMany
    {
        return $this->hasMany(BridgeInstallation::class);
    }

    public function bridgeConfigVersions(): HasMany
    {
        return $this->hasMany(BridgeConfigVersion::class);
    }

    public function pluginUpdateDownloads(): HasMany
    {
        return $this->hasMany(PluginUpdateDownload::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
