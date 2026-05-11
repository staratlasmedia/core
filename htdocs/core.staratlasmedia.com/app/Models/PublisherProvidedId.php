<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublisherProvidedId extends CoreModel
{
    protected function casts(): array
    {
        return [
            'rotated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
