<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommentModerationEvent extends CoreModel
{
    public const TYPE_CREATED = 'created';
    public const TYPE_APPROVED = 'approved';
    public const TYPE_REJECTED = 'rejected';
    public const TYPE_MARKED_SPAM = 'marked_spam';
    public const TYPE_RESTORED = 'restored';
    public const TYPE_TRASHED = 'trashed';
    public const TYPE_DELETED = 'deleted';
    public const TYPE_EDITED = 'edited';
    public const TYPE_REPORTED = 'reported';
    public const TYPE_THREAD_CLOSED = 'thread_closed';
    public const TYPE_THREAD_REOPENED = 'thread_reopened';

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'metadata_json' => 'array',
        ];
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderator_user_id');
    }
}
