<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SesWebhookEvent extends CoreModel
{
    public const UPDATED_AT = null;

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
}
