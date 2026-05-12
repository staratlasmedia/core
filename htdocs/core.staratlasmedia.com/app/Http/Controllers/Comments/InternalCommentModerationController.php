<?php

namespace App\Http\Controllers\Comments;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\CommentModerationEvent;
use App\Models\CommentThread;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InternalCommentModerationController extends Controller
{
    public function moderate(Request $request, string $commentUuid): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'max:32'],
            'reason' => ['nullable', 'string', 'max:2000'],
            'metadata' => ['nullable', 'array'],
        ]);
        $comment = Comment::query()->where('uuid', $commentUuid)->firstOrFail();
        $oldStatus = $comment->status;

        $comment->forceFill([
            'status' => $data['status'],
            'approved_at' => $data['status'] === Comment::STATUS_APPROVED ? now() : $comment->approved_at,
            'rejected_at' => in_array($data['status'], [Comment::STATUS_REJECTED, Comment::STATUS_SPAM], true) ? now() : $comment->rejected_at,
            'trashed_at' => $data['status'] === Comment::STATUS_TRASH ? now() : $comment->trashed_at,
        ])->save();

        $comment->moderationEvents()->create([
            'site_id' => $comment->site_id,
            'moderator_user_id' => $request->user()?->id,
            'event_type' => $this->eventTypeForStatus($data['status']),
            'old_status' => $oldStatus,
            'new_status' => $data['status'],
            'reason' => $data['reason'] ?? null,
            'metadata_json' => $data['metadata'] ?? null,
        ]);

        return response()->json(['status' => 'ok', 'comment_status' => $comment->status])->header('Cache-Control', 'no-store');
    }

    public function updateThread(Request $request, string $threadUuid): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'max:32'],
            'reason' => ['nullable', 'string', 'max:2000'],
            'metadata' => ['nullable', 'array'],
        ]);
        $thread = CommentThread::query()->where('uuid', $threadUuid)->firstOrFail();
        $oldStatus = $thread->status;

        $thread->forceFill([
            'status' => $data['status'],
            'closed_at' => $data['status'] === CommentThread::STATUS_CLOSED ? now() : $thread->closed_at,
            'archived_at' => $data['status'] === CommentThread::STATUS_ARCHIVED ? now() : $thread->archived_at,
        ])->save();

        $eventType = $data['status'] === CommentThread::STATUS_OPEN
            ? CommentModerationEvent::TYPE_THREAD_REOPENED
            : CommentModerationEvent::TYPE_THREAD_CLOSED;

        $thread->comments()->latest()->first()?->moderationEvents()->create([
            'site_id' => $thread->site_id,
            'moderator_user_id' => $request->user()?->id,
            'event_type' => $eventType,
            'old_status' => $oldStatus,
            'new_status' => $data['status'],
            'reason' => $data['reason'] ?? null,
            'metadata_json' => $data['metadata'] ?? null,
        ]);

        return response()->json(['status' => 'ok', 'thread_status' => $thread->status])->header('Cache-Control', 'no-store');
    }

    private function eventTypeForStatus(string $status): string
    {
        return match ($status) {
            Comment::STATUS_APPROVED => CommentModerationEvent::TYPE_APPROVED,
            Comment::STATUS_REJECTED => CommentModerationEvent::TYPE_REJECTED,
            Comment::STATUS_SPAM => CommentModerationEvent::TYPE_MARKED_SPAM,
            Comment::STATUS_TRASH => CommentModerationEvent::TYPE_TRASHED,
            Comment::STATUS_DELETED => CommentModerationEvent::TYPE_DELETED,
            default => CommentModerationEvent::TYPE_EDITED,
        };
    }
}
