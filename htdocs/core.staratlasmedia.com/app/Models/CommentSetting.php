<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommentSetting extends CoreModel
{
    public const SCOPE_GLOBAL = 'global';
    public const SCOPE_SITE = 'site';
    public const SCOPE_PUSH_GROUP = 'push_group';
    public const SCOPE_BRIDGE_INSTALLATION = 'bridge_installation';

    protected function casts(): array
    {
        return [
            'comments_enabled' => 'boolean',
            'require_login' => 'boolean',
            'allow_guest' => 'boolean',
            'require_moderation' => 'boolean',
            'auto_approve_trusted_users' => 'boolean',
            'rate_limit_json' => 'array',
            'banned_words_json' => 'array',
            'moderation_rules_json' => 'array',
            'notify_moderators' => 'boolean',
            'metadata_json' => 'array',
        ];
    }

    public static function scopeKey(string $scope, ?int $id = null): string
    {
        return match ($scope) {
            self::SCOPE_SITE => 'site:'.$id,
            self::SCOPE_PUSH_GROUP => 'push_group:'.$id,
            self::SCOPE_BRIDGE_INSTALLATION => 'bridge_installation:'.$id,
            default => self::SCOPE_GLOBAL,
        };
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function pushGroup(): BelongsTo
    {
        return $this->belongsTo(PushGroup::class);
    }

    public function bridgeInstallation(): BelongsTo
    {
        return $this->belongsTo(BridgeInstallation::class);
    }
}
