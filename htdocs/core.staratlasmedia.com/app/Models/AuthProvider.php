<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class AuthProvider extends CoreModel
{
    protected $hidden = [
        'encrypted_config_json',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_public' => 'boolean',
            'config_json' => 'array',
            'encrypted_config_json' => 'encrypted:array',
            'metadata_json' => 'array',
        ];
    }

    public function siteSettings(): HasMany
    {
        return $this->hasMany(AuthProviderSiteSetting::class);
    }

    public function isEnabledForPublicFlow(): bool
    {
        return $this->status === 'enabled' && $this->is_public;
    }
}
