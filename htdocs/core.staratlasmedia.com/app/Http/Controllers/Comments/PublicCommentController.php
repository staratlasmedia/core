<?php

namespace App\Http\Controllers\Comments;

use App\Http\Controllers\Controller;
use App\Models\BridgeInstallation;
use App\Models\Comment;
use App\Models\CommentThread;
use App\Models\PushGroup;
use App\Models\Site;
use App\Services\Comments\CommentSettingsResolver;
use App\Services\Comments\SourceUrlNormalizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicCommentController extends Controller
{
    public function __construct(
        private readonly CommentSettingsResolver $settings,
        private readonly SourceUrlNormalizer $urls,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'site_code' => ['required', 'string'],
            'push_group' => ['nullable', 'string'],
            'bridge_installation_id' => ['nullable', 'string'],
            'source_url' => ['required', 'url'],
            'parent_id' => ['nullable', 'string'],
            'cursor' => ['nullable', 'integer'],
            'sort' => ['nullable', 'string'],
        ]);

        $site = Site::query()->where('code', $data['site_code'])->firstOrFail();
        $pushGroup = isset($data['push_group'])
            ? PushGroup::query()->where('code', $data['push_group'])->first()
            : $site->pushGroup;
        $installation = isset($data['bridge_installation_id'])
            ? BridgeInstallation::query()->where('uuid', $data['bridge_installation_id'])->first()
            : null;
        $settings = $this->settings->resolve($site->id, $pushGroup?->id, $installation?->id, $data['source_url']);

        if (! $settings->commentsEnabled) {
            return response()->json([
                'status' => 'disabled',
                'comments_enabled' => false,
                'settings' => $settings->toArray(),
                'data' => [],
                'next_cursor' => null,
            ]);
        }

        $sourceUrlHash = $this->urls->hash($data['source_url']);
        $thread = CommentThread::query()
            ->where('site_id', $site->id)
            ->where('source_url_hash', $sourceUrlHash)
            ->first();

        if ($thread === null || $thread->status === CommentThread::STATUS_DISABLED) {
            return response()->json([
                'status' => $thread?->status ?? 'empty',
                'comments_enabled' => $thread?->status !== CommentThread::STATUS_DISABLED,
                'settings' => $settings->toArray(),
                'data' => [],
                'next_cursor' => null,
            ]);
        }

        $query = Comment::query()
            ->with('replies')
            ->where('comment_thread_id', $thread->id)
            ->where('status', Comment::STATUS_APPROVED);

        if (! empty($data['parent_id'])) {
            $parent = Comment::query()->where('uuid', $data['parent_id'])->first();
            $query->where('parent_id', $parent?->id ?? 0);
        } else {
            $query->whereNull('parent_id');
        }

        if (! empty($data['cursor'])) {
            $query->where('id', '>', (int) $data['cursor']);
        }

        $comments = $query
            ->orderBy('id')
            ->limit(26)
            ->get();
        $nextCursor = $comments->count() > 25 ? $comments->last()->id : null;

        return response()->json([
            'status' => $thread->status,
            'comments_enabled' => true,
            'settings' => $settings->toArray(),
            'thread' => [
                'id' => $thread->uuid,
                'source_url' => $thread->source_url,
                'source_title' => $thread->source_title,
                'approved_comments_count' => $thread->approved_comments_count,
            ],
            'data' => $comments->take(25)->map(fn (Comment $comment): array => $this->commentPayload($comment))->values(),
            'next_cursor' => $nextCursor,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function commentPayload(Comment $comment): array
    {
        return [
            'id' => $comment->uuid,
            'parent_id' => $comment->parent?->uuid,
            'root_id' => $comment->root?->uuid,
            'depth' => $comment->depth,
            'author_display_name' => $comment->author_display_name,
            'author_avatar_url' => $comment->author_avatar_url,
            'body' => $comment->body,
            'body_html' => $comment->body_html,
            'status' => $comment->status,
            'replies_count' => $comment->replies_count,
            'likes_count' => $comment->likes_count,
            'reports_count' => $comment->reports_count,
            'created_at' => $comment->created_at?->toISOString(),
        ];
    }
}
