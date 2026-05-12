<?php

namespace App\Services\Comments;

use App\Models\CommentSetting;

class CommentSettingsResult
{
    public function __construct(
        public readonly bool $commentsEnabled,
        public readonly bool $requireLogin,
        public readonly bool $allowGuest,
        public readonly bool $requireModeration,
        public readonly bool $autoApproveTrustedUsers,
        public readonly int $maxDepth,
        public readonly int $maxLength,
        public readonly int $minLength,
        public readonly ?string $defaultSort,
        public readonly ?int $closeAfterDays,
        public readonly ?array $rateLimit,
        public readonly ?array $bannedWords,
        public readonly ?array $moderationRules,
        public readonly bool $notifyModerators,
        public readonly string $scope,
        public readonly string $scopeKey,
        public readonly bool $fromFallback,
    ) {}

    public static function fallback(): self
    {
        return new self(
            commentsEnabled: false,
            requireLogin: true,
            allowGuest: false,
            requireModeration: true,
            autoApproveTrustedUsers: false,
            maxDepth: 3,
            maxLength: 2000,
            minLength: 2,
            defaultSort: null,
            closeAfterDays: null,
            rateLimit: null,
            bannedWords: null,
            moderationRules: null,
            notifyModerators: false,
            scope: CommentSetting::SCOPE_GLOBAL,
            scopeKey: CommentSetting::SCOPE_GLOBAL,
            fromFallback: true,
        );
    }

    public static function fromSetting(CommentSetting $setting): self
    {
        return new self(
            commentsEnabled: (bool) $setting->comments_enabled,
            requireLogin: (bool) $setting->require_login,
            allowGuest: (bool) $setting->allow_guest,
            requireModeration: (bool) $setting->require_moderation,
            autoApproveTrustedUsers: (bool) $setting->auto_approve_trusted_users,
            maxDepth: (int) $setting->max_depth,
            maxLength: (int) $setting->max_length,
            minLength: (int) $setting->min_length,
            defaultSort: $setting->default_sort,
            closeAfterDays: $setting->close_after_days === null ? null : (int) $setting->close_after_days,
            rateLimit: $setting->rate_limit_json,
            bannedWords: $setting->banned_words_json,
            moderationRules: $setting->moderation_rules_json,
            notifyModerators: (bool) $setting->notify_moderators,
            scope: $setting->scope,
            scopeKey: $setting->scope_key,
            fromFallback: false,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'enabled' => $this->commentsEnabled,
            'comments_enabled' => $this->commentsEnabled,
            'require_login' => $this->requireLogin,
            'requireLogin' => $this->requireLogin,
            'allow_guest' => $this->allowGuest,
            'allowGuest' => $this->allowGuest,
            'require_moderation' => $this->requireModeration,
            'requireModeration' => $this->requireModeration,
            'auto_approve_trusted_users' => $this->autoApproveTrustedUsers,
            'max_depth' => $this->maxDepth,
            'maxDepth' => $this->maxDepth,
            'max_length' => $this->maxLength,
            'maxLength' => $this->maxLength,
            'min_length' => $this->minLength,
            'minLength' => $this->minLength,
            'default_sort' => $this->defaultSort,
            'close_after_days' => $this->closeAfterDays,
            'scope' => $this->scope,
            'scope_key' => $this->scopeKey,
            'from_fallback' => $this->fromFallback,
        ];
    }
}
