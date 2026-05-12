<?php

namespace App\Http\Controllers\Comments;

use App\Http\Controllers\Controller;
use App\Models\BridgeInstallation;
use App\Models\Comment;
use App\Models\CommentModerationEvent;
use App\Models\CommentReaction;
use App\Models\CommentReport;
use App\Models\CommentThread;
use App\Services\Comments\CommentSettingsResolver;
use App\Services\Comments\SourceUrlNormalizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BridgeCommentController extends Controller
{
    public function __construct(
        private readonly CommentSettingsResolver $settings,
        private readonly SourceUrlNormalizer $urls,
    ) {}

    public function store(Request $request): JsonResponse
    {
        /** @var BridgeInstallation $installation */
        $installation = $request->attributes->get('bridge_installation');
        $data = $request->validate([
            'source_url' => ['required', 'url'],
            'source_title' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'parent_id' => ['nullable', 'string'],
            'user' => ['nullable', 'array'],
            'user.ppid' => ['nullable', 'string', 'max:255'],
            'user.name' => ['nullable', 'string', 'max:255'],
            'user.email' => ['nullable', 'email'],
            'user.avatar_url' => ['nullable', 'url'],
            'user.provider' => ['nullable', 'string', 'max:64'],
            'language' => ['nullable', 'string', 'max:16'],
            'section' => ['nullable', 'string', 'max:255'],
            'wp_terms_json' => ['nullable', 'array'],
            'metadata' => ['nullable', 'array'],
        ]);

        $settings = $this->settings->resolve(
            siteId: $installation->site_id,
            pushGroupId: $installation->push_group_id,
            bridgeInstallationId: $installation->id,
            sourceUrl: $data['source_url'],
            language: $data['language'] ?? $installation->language,
            section: $data['section'] ?? $installation->section,
        );

        if (! $settings->commentsEnabled) {
            return response()->json(['status' => 'feature_disabled', 'message' => 'Comments are disabled.'], 403);
        }

        $userPayload = $data['user'] ?? null;

        if ($settings->requireLogin && empty($userPayload['ppid'])) {
            return response()->json(['status' => 'login_required', 'message' => 'Login is required to comment.'], 401);
        }

        if (! $settings->allowGuest && empty($userPayload['ppid'])) {
            return response()->json(['status' => 'guest_not_allowed', 'message' => 'Guest comments are not enabled.'], 403);
        }

        $body = trim($data['body']);

        if (mb_strlen($body) < $settings->minLength || mb_strlen($body) > $settings->maxLength) {
            return response()->json(['status' => 'invalid_length', 'message' => 'Comment length is outside the configured limits.'], 422);
        }

        $sourceUrl = $this->urls->normalize($data['source_url']);
        $sourceUrlHash = $this->urls->hash($sourceUrl);

        return DB::transaction(function () use ($request, $installation, $data, $settings, $userPayload, $body, $sourceUrl, $sourceUrlHash): JsonResponse {
            $thread = CommentThread::query()->firstOrCreate(
                [
                    'site_id' => $installation->site_id,
                    'source_url_hash' => $sourceUrlHash,
                ],
                [
                    'push_group_id' => $installation->push_group_id,
                    'bridge_installation_id' => $installation->id,
                    'source_url' => $sourceUrl,
                    'source_title' => $data['source_title'] ?? null,
                    'language' => $data['language'] ?? $installation->language,
                    'section' => $data['section'] ?? $installation->section,
                    'status' => CommentThread::STATUS_OPEN,
                    'wp_terms_json' => $data['wp_terms_json'] ?? null,
                ],
            );

            if (in_array($thread->status, [CommentThread::STATUS_CLOSED, CommentThread::STATUS_ARCHIVED, CommentThread::STATUS_DISABLED], true)) {
                return response()->json(['status' => 'thread_closed', 'message' => 'This comment thread is not open.'], 409);
            }

            $parent = null;
            $depth = 0;
            $rootId = null;

            if (! empty($data['parent_id'])) {
                $parent = Comment::query()
                    ->where('uuid', $data['parent_id'])
                    ->where('comment_thread_id', $thread->id)
                    ->firstOrFail();
                $depth = (int) $parent->depth + 1;
                $rootId = $parent->root_id ?: $parent->id;

                if ($depth > $settings->maxDepth) {
                    return response()->json(['status' => 'max_depth_exceeded', 'message' => 'Comment nesting limit exceeded.'], 422);
                }
            }

            $status = $settings->requireModeration && ! $settings->autoApproveTrustedUsers
                ? Comment::STATUS_PENDING
                : Comment::STATUS_APPROVED;

            $comment = Comment::query()->create([
                'site_id' => $installation->site_id,
                'site_origin_id' => $installation->site_origin_id,
                'push_group_id' => $installation->push_group_id,
                'bridge_installation_id' => $installation->id,
                'comment_thread_id' => $thread->id,
                'parent_id' => $parent?->id,
                'root_id' => $rootId,
                'depth' => $depth,
                'external_post_url_hash' => $sourceUrlHash,
                'source_url' => $sourceUrl,
                'source_url_hash' => $sourceUrlHash,
                'author_display_name' => $userPayload['name'] ?? null,
                'author_email_hash' => ! empty($userPayload['email']) ? hash('sha256', strtolower((string) $userPayload['email'])) : null,
                'author_avatar_url' => $userPayload['avatar_url'] ?? null,
                'body' => $body,
                'body_html' => nl2br(e(strip_tags($body))),
                'status' => $status,
                'ip_hash' => $this->hashNullable($request->ip()),
                'user_agent_hash' => $this->hashNullable($request->userAgent()),
                'created_by_provider' => $userPayload['provider'] ?? null,
                'approved_at' => $status === Comment::STATUS_APPROVED ? now() : null,
                'metadata_json' => [
                    'bridge_user' => $userPayload,
                    'metadata' => $data['metadata'] ?? null,
                ],
            ]);

            if ($parent instanceof Comment) {
                $parent->increment('replies_count');
            }

            $thread->increment('comments_count');
            $thread->increment($status === Comment::STATUS_APPROVED ? 'approved_comments_count' : 'pending_comments_count');
            $thread->forceFill(['last_commented_at' => now()])->save();

            $comment->moderationEvents()->create([
                'site_id' => $installation->site_id,
                'event_type' => CommentModerationEvent::TYPE_CREATED,
                'new_status' => $status,
                'metadata_json' => ['source' => 'bridge'],
            ]);

            return response()->json([
                'status' => $status,
                'comment' => [
                    'id' => $comment->uuid,
                    'thread_id' => $thread->uuid,
                    'status' => $comment->status,
                    'created_at' => $comment->created_at?->toISOString(),
                ],
            ], 201)->header('Cache-Control', 'no-store');
        });
    }

    public function react(Request $request, string $commentUuid): JsonResponse
    {
        /** @var BridgeInstallation $installation */
        $installation = $request->attributes->get('bridge_installation');
        $data = $request->validate([
            'reaction_type' => ['nullable', 'string', 'max:32'],
            'user' => ['nullable', 'array'],
            'user.ppid' => ['nullable', 'string', 'max:255'],
            'metadata' => ['nullable', 'array'],
        ]);
        $comment = Comment::query()->where('uuid', $commentUuid)->firstOrFail();
        $settings = $this->settings->resolve($installation->site_id, $installation->push_group_id, $installation->id, $comment->source_url);

        if (! $settings->commentsEnabled) {
            return response()->json(['status' => 'feature_disabled'], 403);
        }

        $reactionType = $data['reaction_type'] ?? CommentReaction::TYPE_LIKE;
        $anonymousId = ! empty($data['user']['ppid']) ? hash('sha256', (string) $data['user']['ppid']) : null;
        $reaction = CommentReaction::query()->firstOrCreate(
            [
                'comment_id' => $comment->id,
                'anonymous_id' => $anonymousId,
                'reaction_type' => $reactionType,
            ],
            [
                'site_id' => $installation->site_id,
                'ip_hash' => $this->hashNullable($request->ip()),
                'user_agent_hash' => $this->hashNullable($request->userAgent()),
                'metadata_json' => $data['metadata'] ?? null,
            ],
        );

        if ($reaction->wasRecentlyCreated && $reactionType === CommentReaction::TYPE_LIKE) {
            $comment->increment('likes_count');
        }

        return response()->json(['status' => 'ok', 'reaction_type' => $reactionType])->header('Cache-Control', 'no-store');
    }

    public function destroyReaction(Request $request, string $commentUuid, string $reactionType): JsonResponse
    {
        $data = $request->validate([
            'user' => ['nullable', 'array'],
            'user.ppid' => ['nullable', 'string', 'max:255'],
        ]);
        $comment = Comment::query()->where('uuid', $commentUuid)->firstOrFail();
        $anonymousId = ! empty($data['user']['ppid']) ? hash('sha256', (string) $data['user']['ppid']) : null;
        $deleted = CommentReaction::query()
            ->where('comment_id', $comment->id)
            ->where('anonymous_id', $anonymousId)
            ->where('reaction_type', $reactionType)
            ->delete();

        if ($deleted > 0 && $reactionType === CommentReaction::TYPE_LIKE && $comment->likes_count > 0) {
            $comment->decrement('likes_count');
        }

        return response()->json(['status' => 'ok'])->header('Cache-Control', 'no-store');
    }

    public function report(Request $request, string $commentUuid): JsonResponse
    {
        /** @var BridgeInstallation $installation */
        $installation = $request->attributes->get('bridge_installation');
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:64'],
            'message' => ['nullable', 'string', 'max:2000'],
            'user' => ['nullable', 'array'],
            'metadata' => ['nullable', 'array'],
        ]);
        $comment = Comment::query()->where('uuid', $commentUuid)->firstOrFail();

        $report = $comment->reports()->create([
            'site_id' => $installation->site_id,
            'reason' => $data['reason'] ?? 'other',
            'message' => $data['message'] ?? null,
            'status' => CommentReport::STATUS_OPEN,
            'ip_hash' => $this->hashNullable($request->ip()),
            'user_agent_hash' => $this->hashNullable($request->userAgent()),
            'metadata_json' => [
                'bridge_user' => $data['user'] ?? null,
                'metadata' => $data['metadata'] ?? null,
            ],
        ]);

        $comment->increment('reports_count');
        $comment->thread?->increment('reported_comments_count');
        $comment->moderationEvents()->create([
            'site_id' => $installation->site_id,
            'event_type' => CommentModerationEvent::TYPE_REPORTED,
            'metadata_json' => ['report_id' => $report->id],
        ]);

        return response()->json(['status' => 'open', 'report_id' => $report->id], 201)->header('Cache-Control', 'no-store');
    }

    private function hashNullable(?string $value): ?string
    {
        return $value === null || $value === '' ? null : hash('sha256', $value);
    }
}
