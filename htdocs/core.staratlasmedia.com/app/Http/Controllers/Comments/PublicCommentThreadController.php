<?php

namespace App\Http\Controllers\Comments;

use App\Http\Controllers\Controller;
use App\Models\BridgeInstallation;
use App\Models\CommentThread;
use App\Models\PushGroup;
use App\Models\Site;
use App\Services\Comments\CommentSettingsResolver;
use App\Services\Comments\SourceUrlNormalizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicCommentThreadController extends Controller
{
    public function __construct(
        private readonly CommentSettingsResolver $settings,
        private readonly SourceUrlNormalizer $urls,
    ) {}

    public function resolve(Request $request): JsonResponse
    {
        $data = $request->validate([
            'site_code' => ['required', 'string'],
            'push_group' => ['nullable', 'string'],
            'bridge_installation_id' => ['nullable', 'string'],
            'source_url' => ['required', 'url'],
            'source_title' => ['nullable', 'string', 'max:500'],
            'language' => ['nullable', 'string', 'max:16'],
            'section' => ['nullable', 'string', 'max:255'],
        ]);

        $site = Site::query()->where('code', $data['site_code'])->firstOrFail();
        $pushGroup = isset($data['push_group'])
            ? PushGroup::query()->where('code', $data['push_group'])->first()
            : $site->pushGroup;
        $installation = isset($data['bridge_installation_id'])
            ? BridgeInstallation::query()->where('uuid', $data['bridge_installation_id'])->first()
            : null;
        $settings = $this->settings->resolve(
            siteId: $site->id,
            pushGroupId: $pushGroup?->id,
            bridgeInstallationId: $installation?->id,
            sourceUrl: $data['source_url'],
            language: $data['language'] ?? null,
            section: $data['section'] ?? null,
        );

        if (! $settings->commentsEnabled) {
            return response()->json([
                'status' => 'disabled',
                'comments_enabled' => false,
                'settings' => $settings->toArray(),
                'thread' => null,
            ]);
        }

        $sourceUrl = $this->urls->normalize($data['source_url']);
        $thread = CommentThread::query()->firstOrCreate(
            [
                'site_id' => $site->id,
                'source_url_hash' => $this->urls->hash($sourceUrl),
            ],
            [
                'push_group_id' => $pushGroup?->id,
                'bridge_installation_id' => $installation?->id,
                'source_url' => $sourceUrl,
                'source_title' => $data['source_title'] ?? null,
                'language' => $data['language'] ?? $site->language,
                'section' => $data['section'] ?? null,
                'status' => CommentThread::STATUS_OPEN,
            ],
        );

        return response()->json([
            'status' => $thread->status,
            'comments_enabled' => $thread->status !== CommentThread::STATUS_DISABLED,
            'settings' => $settings->toArray(),
            'thread' => $this->threadPayload($thread),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function threadPayload(CommentThread $thread): array
    {
        return [
            'id' => $thread->uuid,
            'source_url' => $thread->source_url,
            'source_url_hash' => $thread->source_url_hash,
            'source_title' => $thread->source_title,
            'status' => $thread->status,
            'comments_count' => $thread->comments_count,
            'approved_comments_count' => $thread->approved_comments_count,
            'pending_comments_count' => $thread->pending_comments_count,
            'reported_comments_count' => $thread->reported_comments_count,
            'last_commented_at' => $thread->last_commented_at?->toISOString(),
        ];
    }
}
