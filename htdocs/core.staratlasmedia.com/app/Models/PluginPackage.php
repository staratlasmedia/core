<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class PluginPackage extends CoreModel
{
    protected function casts(): array
    {
        return [
            'metadata_json' => 'array',
        ];
    }

    public function releases(): HasMany
    {
        return $this->hasMany(PluginRelease::class);
    }
}
