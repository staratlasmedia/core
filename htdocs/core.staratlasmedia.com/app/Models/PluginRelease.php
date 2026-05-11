<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PluginRelease extends CoreModel
{
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'revoked_at' => 'datetime',
            'metadata_json' => 'array',
        ];
    }

    public function pluginPackage(): BelongsTo
    {
        return $this->belongsTo(PluginPackage::class);
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(PluginUpdateDownload::class);
    }
}
