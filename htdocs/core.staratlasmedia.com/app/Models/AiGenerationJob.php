<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiGenerationJob extends CoreModel
{
    protected function casts(): array
    {
        return [
            'input_json' => 'array',
            'output_json' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
            'metadata_json' => 'array',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(AiProvider::class, 'ai_provider_id');
    }

    public function modelProfile(): BelongsTo
    {
        return $this->belongsTo(AiModelProfile::class, 'ai_model_profile_id');
    }
}
