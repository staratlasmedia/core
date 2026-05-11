<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommentReport extends CoreModel
{
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
