<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SiteOrigin extends CoreModel
{
    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
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
}
