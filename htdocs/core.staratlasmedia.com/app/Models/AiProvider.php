<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class AiProvider extends CoreModel
{
    protected $hidden = ['api_key_encrypted'];

    protected function casts(): array
    {
        return [
            'api_key_encrypted' => 'encrypted',
            'rate_limit_json' => 'array',
            'cost_tracking_enabled' => 'boolean',
            'last_test_result_json' => 'array',
            'last_tested_at' => 'datetime',
            'metadata_json' => 'array',
        ];
    }

    public function modelProfiles(): HasMany
    {
        return $this->hasMany(AiModelProfile::class);
    }
}
