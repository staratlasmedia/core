<?php

namespace Tests\Feature;

use App\Filament\Pages\ModerationCenter;
use App\Filament\Resources\CommentResource;
use App\Filament\Resources\CommentSettingResource;
use App\Models\BridgeInstallation;
use App\Models\Comment;
use App\Models\CommentModerationEvent;
use App\Models\CommentSetting;
use App\Models\CommentThread;
use App\Models\PushGroup;
use App\Models\Site;
use App\Models\SiteOrigin;
use App\Services\Bridge\BridgeTokenFactory;
use App\Services\Comments\CommentSettingsResolver;
use App\Services\Comments\SourceUrlNormalizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CommentsPhase8Test extends TestCase
{
    use RefreshDatabase;

    public function test_phase_eight_schema_is_additive_and_keeps_legacy_hash(): void
    {
        $this->assertTrue(Schema::hasTable('comment_threads'));
        $this->assertTrue(Schema::hasTable('comment_settings'));
        $this->assertTrue(Schema::hasColumn('comments', 'external_post_url_hash'));

        foreach (['uuid', 'comment_thread_id', 'source_url_hash', 'root_id', 'depth', 'ip_hash', 'user_agent_hash', 'author_email_hash', 'metadata_json'] as $column) {
            $this->assertTrue(Schema::hasColumn('comments', $column), "Missing comments.{$column}.");
        }

        $this->assertTrue(Schema::hasColumn('comment_reports', 'site_id'));
        $this->assertTrue(Schema::hasColumn('comment_reactions', 'metadata_json'));
        $this->assertTrue(Schema::hasColumn('comment_moderation_events', 'reason'));
        $this->assertFalse(Schema::hasColumn('comments', 'external_post_url'));
        $this->assertFalse(Schema::hasColumn('comments', 'post_id'));
    }

    public function test_source_url_normalization_and_settings_fallback_and_precedence(): void
    {
        [$installation] = $this->claimedInstallation('/');
        $normalizer = app(SourceUrlNormalizer::class);
        $resolver = app(CommentSettingsResolver::class);

        $this->assertSame(
            'https://www.clubalfa.it/post/?a=1&b=2',
            $normalizer->normalize('HTTPS://WWW.CLUBALFA.IT/post?b=2&a=1#comments'),
        );
        $this->assertFalse($resolver->resolve($installation->site_id, $installation->push_group_id, $installation->id)->commentsEnabled);

        CommentSetting::query()->create([
            'scope' => CommentSetting::SCOPE_SITE,
            'scope_key' => CommentSetting::scopeKey(CommentSetting::SCOPE_SITE, $installation->site_id),
            'site_id' => $installation->site_id,
            'comments_enabled' => true,
            'require_login' => true,
            'allow_guest' => false,
            'require_moderation' => true,
        ]);
        CommentSetting::query()->create([
            'scope' => CommentSetting::SCOPE_BRIDGE_INSTALLATION,
            'scope_key' => CommentSetting::scopeKey(CommentSetting::SCOPE_BRIDGE_INSTALLATION, $installation->id),
            'bridge_installation_id' => $installation->id,
            'comments_enabled' => false,
            'require_login' => true,
            'allow_guest' => false,
            'require_moderation' => true,
        ]);

        $resolved = $resolver->resolve($installation->site_id, $installation->push_group_id, $installation->id);

        $this->assertFalse($resolved->commentsEnabled);
        $this->assertSame('bridge_installation:'.$installation->id, $resolved->scopeKey);
    }

    public function test_public_read_returns_disabled_state_without_explicit_settings(): void
    {
        $this->siteAndOrigin('/');

        $this->getJson('/api/v1/comments/threads/resolve?site_code=clubalfa_it&source_url='.rawurlencode('https://www.clubalfa.it/post/'))
            ->assertOk()
            ->assertJsonPath('status', 'disabled')
            ->assertJsonPath('comments_enabled', false);
    }

    public function test_bridge_comment_write_requires_hmac_settings_and_login(): void
    {
        [$installation, $secret] = $this->claimedInstallation('/');
        $this->enableComments($installation);

        $payload = [
            'source_url' => 'https://www.clubalfa.it/post/?utm_source=test',
            'source_title' => 'Test post',
            'body' => 'Commento di prova',
            'language' => 'it',
            'section' => 'main',
        ];

        $this->postJson('/api/bridge/comments', $payload)->assertUnauthorized();

        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        $this->withHeaders($this->hmacHeaders($installation, $secret, 'POST', '/api/bridge/comments', $body))
            ->json('POST', '/api/bridge/comments', $payload)
            ->assertUnauthorized()
            ->assertJsonPath('status', 'login_required');

        $payload['user'] = [
            'ppid' => 'ppid_1_test',
            'name' => 'Mario Rossi',
            'email' => 'mario@example.test',
            'provider' => 'passkey',
        ];
        $body = json_encode($payload, JSON_THROW_ON_ERROR);

        $this->withHeaders($this->hmacHeaders($installation, $secret, 'POST', '/api/bridge/comments', $body))
            ->json('POST', '/api/bridge/comments', $payload)
            ->assertCreated()
            ->assertJsonPath('status', Comment::STATUS_PENDING);

        $comment = Comment::query()->firstOrFail();
        $thread = CommentThread::query()->firstOrFail();

        $this->assertSame($thread->id, $comment->comment_thread_id);
        $this->assertSame(app(SourceUrlNormalizer::class)->hash($payload['source_url']), $comment->source_url_hash);
        $this->assertSame($comment->source_url_hash, $comment->external_post_url_hash);
        $this->assertNotSame('mario@example.test', $comment->author_email_hash);
        $this->assertNotNull($comment->ip_hash);
        $this->assertSame(1, CommentModerationEvent::query()->where('event_type', CommentModerationEvent::TYPE_CREATED)->count());
    }

    public function test_public_read_only_returns_approved_comments(): void
    {
        [$installation, $secret] = $this->claimedInstallation('/');
        $this->enableComments($installation, requireModeration: false);
        $sourceUrl = 'https://www.clubalfa.it/post/';
        $payload = [
            'source_url' => $sourceUrl,
            'body' => 'Approved comment',
            'user' => ['ppid' => 'ppid_1_test', 'name' => 'Reader'],
        ];
        $body = json_encode($payload, JSON_THROW_ON_ERROR);

        $this->withHeaders($this->hmacHeaders($installation, $secret, 'POST', '/api/bridge/comments', $body))
            ->json('POST', '/api/bridge/comments', $payload)
            ->assertCreated()
            ->assertJsonPath('status', Comment::STATUS_APPROVED);

        Comment::query()->create([
            'site_id' => $installation->site_id,
            'comment_thread_id' => CommentThread::query()->firstOrFail()->id,
            'external_post_url_hash' => app(SourceUrlNormalizer::class)->hash($sourceUrl),
            'source_url' => $sourceUrl,
            'source_url_hash' => app(SourceUrlNormalizer::class)->hash($sourceUrl),
            'body' => 'Pending hidden',
            'status' => Comment::STATUS_PENDING,
        ]);

        $this->getJson('/api/v1/comments?site_code=clubalfa_it&bridge_installation_id='.$installation->uuid.'&source_url='.rawurlencode($sourceUrl))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.body', 'Approved comment');
    }

    public function test_filament_comment_surfaces_are_registered(): void
    {
        $this->assertTrue(class_exists(ModerationCenter::class));
        $this->assertTrue(CommentSettingResource::canCreate());
        $this->assertSame(Comment::class, CommentResource::getModel());
    }

    /**
     * @return array{Site, SiteOrigin}
     */
    private function siteAndOrigin(string $pathPrefix): array
    {
        $pushGroup = PushGroup::query()->where('code', 'clubalfa_it')->firstOrFail();
        $site = Site::query()->create([
            'code' => 'clubalfa_it',
            'name' => 'ClubAlfa IT',
            'canonical_origin' => 'https://www.clubalfa.it',
            'language' => 'it',
            'push_group' => 'clubalfa_it',
            'push_group_id' => $pushGroup->id,
        ]);
        $origin = $site->origins()->create([
            'origin' => 'https://www.clubalfa.it',
            'path_prefix' => $pathPrefix,
            'is_primary' => $pathPrefix === '/',
        ]);

        return [$site, $origin];
    }

    /**
     * @return array{BridgeInstallation, string}
     */
    private function claimedInstallation(string $pathPrefix): array
    {
        [$site, $origin] = $this->siteAndOrigin($pathPrefix);
        $token = app(BridgeTokenFactory::class)->create($site, $site->pushGroup, $origin)['token'];
        $response = $this->postJson('/api/bridge/setup/claim', [
            'setup_token' => $token,
            'wp_home_url' => 'https://www.clubalfa.it',
            'wp_site_url' => 'https://www.clubalfa.it',
            'detected_origin' => 'https://www.clubalfa.it',
            'detected_base_path' => $pathPrefix,
            'plugin_version' => '0.1.0',
        ])->assertCreated();

        return [BridgeInstallation::query()->where('uuid', $response->json('bridge_installation_id'))->firstOrFail(), $response->json('bridge_secret')];
    }

    private function enableComments(BridgeInstallation $installation, bool $requireModeration = true): void
    {
        CommentSetting::query()->create([
            'scope' => CommentSetting::SCOPE_BRIDGE_INSTALLATION,
            'scope_key' => CommentSetting::scopeKey(CommentSetting::SCOPE_BRIDGE_INSTALLATION, $installation->id),
            'bridge_installation_id' => $installation->id,
            'comments_enabled' => true,
            'require_login' => true,
            'allow_guest' => false,
            'require_moderation' => $requireModeration,
            'max_depth' => 3,
            'max_length' => 2000,
            'min_length' => 2,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function hmacHeaders(BridgeInstallation $installation, string $secret, string $method, string $path, string $body = ''): array
    {
        $timestamp = (string) now()->timestamp;
        $nonce = 'test-nonce';
        $canonical = implode("\n", [
            $method,
            $path,
            $timestamp,
            $nonce,
            hash('sha256', $body),
        ]);

        return [
            'X-Core-Bridge-Id' => $installation->uuid,
            'X-Core-Timestamp' => $timestamp,
            'X-Core-Nonce' => $nonce,
            'X-Core-Signature' => hash_hmac('sha256', $canonical, $secret),
        ];
    }
}
