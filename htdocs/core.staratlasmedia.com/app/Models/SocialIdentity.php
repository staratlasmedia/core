<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialIdentity extends CoreModel
{
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
