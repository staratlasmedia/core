<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsletterSetting extends CoreModel
{
    public const SCOPE_GLOBAL = 'global';
    public const SCOPE_SITE = 'site';
    public const SCOPE_PUSH_GROUP = 'push_group';
    public const SCOPE_BRIDGE_INSTALLATION = 'bridge_installation';

    protected function casts(): array
    {
        return [
            'newsletter_enabled' => 'boolean',
            'double_opt_in' => 'boolean',
            'require_consent' => 'boolean',
            'send_enabled' => 'boolean',
            'allow_import' => 'boolean',
            'ai_generation_enabled' => 'boolean',
            'rss_import_enabled' => 'boolean',
            'wordpress_api_import_enabled' => 'boolean',
            'automatic_digest_enabled' => 'boolean',
            'rate_limit_json' => 'array',
            'editorial_workflow_json' => 'array',
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

    public function defaultList(): BelongsTo
    {
        return $this->belongsTo(NewsletterList::class, 'default_list_id');
    }

    public function defaultSenderIdentity(): BelongsTo
    {
        return $this->belongsTo(EmailSenderIdentity::class, 'default_sender_identity_id');
    }
}
