<?php

namespace App\Services\Newsletter;

use App\Models\EmailSenderIdentity;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterDigestRecipe;
use App\Models\NewsletterImportBatch;
use App\Models\NewsletterSetting;
use App\Services\Newsletter\Exceptions\NewsletterOperationBlocked;

class NewsletterOperationalGate
{
    public function __construct(private readonly NewsletterSettingsResolver $settings) {}

    public function settingsFor(?int $siteId, ?int $pushGroupId = null, ?int $bridgeInstallationId = null): NewsletterSettingsResult
    {
        return $this->settings->resolve($siteId, $pushGroupId, $bridgeInstallationId);
    }

    public function ensureImportCommitAllowed(NewsletterImportBatch $batch): void
    {
        $settings = $this->settingsFor($batch->site_id, $batch->push_group_id, $batch->bridge_installation_id);

        if (! $settings->newsletterEnabled || ! $settings->allowImport) {
            throw NewsletterOperationBlocked::forReason('newsletter_import_disabled');
        }
    }

    public function ensureDigestDraftAllowed(NewsletterDigestRecipe $recipe): void
    {
        $settings = $this->settingsFor($recipe->site_id, $recipe->push_group_id);

        if (! $settings->newsletterEnabled) {
            throw NewsletterOperationBlocked::forReason('newsletter_disabled');
        }
    }

    public function ensureContentPersistAllowed(?int $siteId, ?int $pushGroupId, ?int $bridgeInstallationId, string $sourceType): void
    {
        $settings = $this->settingsFor($siteId, $pushGroupId, $bridgeInstallationId);
        $allowed = match ($sourceType) {
            'rss', 'atom' => $settings->rssImportEnabled,
            'wordpress_rest' => $settings->wordpressApiImportEnabled,
            default => false,
        };

        if (! $settings->newsletterEnabled || ! $allowed) {
            throw NewsletterOperationBlocked::forReason('content_source_persist_disabled');
        }
    }

    public function ensureAiDraftAllowed(NewsletterCampaign $campaign): void
    {
        $settings = $this->settingsFor($campaign->site_id, $campaign->push_group_id);

        if (! $settings->newsletterEnabled || ! $settings->aiGenerationEnabled) {
            throw NewsletterOperationBlocked::forReason('ai_generation_disabled');
        }
    }

    public function checkControlledTestSend(EmailSenderIdentity $identity): ?string
    {
        if (! (bool) config('core.newsletter.send_enabled', false)) {
            return 'global_newsletter_send_disabled';
        }

        if (! $identity->send_enabled || ! $identity->test_send_enabled || $identity->status !== 'active') {
            return 'sender_identity_disabled';
        }

        $settings = $this->settingsFor($identity->site_id);

        if ($identity->site_id !== null && (! $settings->newsletterEnabled || ! $settings->sendEnabled)) {
            return 'newsletter_send_disabled';
        }

        return null;
    }

    public static function defaultSettingValues(): array
    {
        return [
            'scope' => NewsletterSetting::SCOPE_GLOBAL,
            'scope_key' => NewsletterSetting::SCOPE_GLOBAL,
            'newsletter_enabled' => false,
            'double_opt_in' => true,
            'require_consent' => true,
            'send_enabled' => false,
            'allow_import' => false,
            'ai_generation_enabled' => false,
            'rss_import_enabled' => false,
            'wordpress_api_import_enabled' => false,
            'automatic_digest_enabled' => false,
        ];
    }
}
