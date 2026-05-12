<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiModelProfile extends CoreModel
{
    protected function casts(): array
    {
        return [
            'response_format_json' => 'array',
            'metadata_json' => 'array',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(AiProvider::class, 'ai_provider_id');
    }
}
