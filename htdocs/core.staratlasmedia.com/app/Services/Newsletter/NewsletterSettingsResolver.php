<?php

namespace App\Services\Newsletter;

use App\Models\NewsletterSetting;

class NewsletterSettingsResolver
{
    public function resolve(
        ?int $siteId,
        ?int $pushGroupId = null,
        ?int $bridgeInstallationId = null,
    ): NewsletterSettingsResult {
        $scopeKeys = array_values(array_filter([
            $bridgeInstallationId !== null ? NewsletterSetting::scopeKey(NewsletterSetting::SCOPE_BRIDGE_INSTALLATION, $bridgeInstallationId) : null,
            $pushGroupId !== null ? NewsletterSetting::scopeKey(NewsletterSetting::SCOPE_PUSH_GROUP, $pushGroupId) : null,
            $siteId !== null ? NewsletterSetting::scopeKey(NewsletterSetting::SCOPE_SITE, $siteId) : null,
            NewsletterSetting::SCOPE_GLOBAL,
        ]));

        $settings = NewsletterSetting::query()
            ->whereIn('scope_key', $scopeKeys)
            ->get()
            ->keyBy('scope_key');

        foreach ($scopeKeys as $scopeKey) {
            $setting = $settings->get($scopeKey);

            if ($setting instanceof NewsletterSetting) {
                return NewsletterSettingsResult::fromSetting($setting);
            }
        }

        return NewsletterSettingsResult::fallback();
    }
}
