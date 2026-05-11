<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommentReaction extends CoreModel
{
    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
