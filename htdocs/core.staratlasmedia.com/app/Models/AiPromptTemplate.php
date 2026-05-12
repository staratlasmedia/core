<?php

namespace App\Models;

class AiPromptTemplate extends CoreModel
{
    protected function casts(): array
    {
        return [
            'variables_json' => 'array',
            'metadata_json' => 'array',
        ];
    }
}
