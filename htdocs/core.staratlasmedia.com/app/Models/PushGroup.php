<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PushGroup extends CoreModel
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'pwa_config_json' => 'array',
            'metadata_json' => 'array',
        ];
    }

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
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
}
