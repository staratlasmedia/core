<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookEvent extends CoreModel
{
    public const UPDATED_AT = null;

    protected $hidden = [
        'signature_hash',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function apiClient(): BelongsTo
    {
        return $this->belongsTo(ApiClient::class);
    }
}
