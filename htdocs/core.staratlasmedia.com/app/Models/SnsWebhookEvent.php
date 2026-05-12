<?php

namespace App\Models;

class SnsWebhookEvent extends CoreModel
{
    protected function casts(): array
    {
        return [
            'raw_payload_json' => 'array',
            'received_at' => 'datetime',
            'verified_at' => 'datetime',
            'processed_at' => 'datetime',
            'metadata_json' => 'array',
        ];
    }
}
