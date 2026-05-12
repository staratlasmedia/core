<?php

namespace App\Services\Comments;

use App\Models\CommentSetting;

class CommentSettingsResolver
{
    public function resolve(
        ?int $siteId,
        ?int $pushGroupId = null,
        ?int $bridgeInstallationId = null,
        ?string $sourceUrl = null,
        ?string $language = null,
        ?string $section = null,
    ): CommentSettingsResult {
        $scopeKeys = array_values(array_filter([
            $bridgeInstallationId !== null ? CommentSetting::scopeKey(CommentSetting::SCOPE_BRIDGE_INSTALLATION, $bridgeInstallationId) : null,
            $pushGroupId !== null ? CommentSetting::scopeKey(CommentSetting::SCOPE_PUSH_GROUP, $pushGroupId) : null,
            $siteId !== null ? CommentSetting::scopeKey(CommentSetting::SCOPE_SITE, $siteId) : null,
            CommentSetting::SCOPE_GLOBAL,
        ]));

        $settings = CommentSetting::query()
            ->whereIn('scope_key', $scopeKeys)
            ->get()
            ->keyBy('scope_key');

        foreach ($scopeKeys as $scopeKey) {
            $setting = $settings->get($scopeKey);

            if ($setting instanceof CommentSetting) {
                return CommentSettingsResult::fromSetting($setting);
            }
        }

        return CommentSettingsResult::fallback();
    }
}
