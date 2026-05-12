<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthProviderSiteSetting extends CoreModel
{
    protected $hidden = [
        'encrypted_config_json',
    ];

    protected function casts(): array
    {
        return [
            'config_json' => 'array',
            'encrypted_config_json' => 'encrypted:array',
        ];
    }

    public function authProvider(): BelongsTo
    {
        return $this->belongsTo(AuthProvider::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function pushGroup(): BelongsTo
    {
        return $this->belongsTo(PushGroup::class);
    }

    public function bridgeInstallation(): BelongsTo
    {
        return $this->belongsTo(BridgeInstallation::class);
    }
}
