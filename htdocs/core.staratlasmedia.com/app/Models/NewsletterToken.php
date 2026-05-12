<?php

namespace App\Models;

class NewsletterToken extends CoreModel
{
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
            'metadata_json' => 'array',
        ];
    }
}
