<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommentModerationEvent extends CoreModel
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

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderator_user_id');
    }
}
