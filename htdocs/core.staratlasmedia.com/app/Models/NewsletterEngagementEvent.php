<?php

namespace App\Models;

class NewsletterEngagementEvent extends CoreModel
{
    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'metadata_json' => 'array',
        ];
    }
}
