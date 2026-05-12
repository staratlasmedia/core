<?php

namespace App\Services\Newsletter;

use App\Models\NewsletterSetting;

class NewsletterSettingsResult
{
    public function __construct(
        public readonly bool $newsletterEnabled,
        public readonly bool $doubleOptIn,
        public readonly bool $requireConsent,
        public readonly bool $sendEnabled,
        public readonly bool $allowImport,
        public readonly bool $aiGenerationEnabled,
        public readonly bool $rssImportEnabled,
        public readonly bool $wordpressApiImportEnabled,
        public readonly bool $automaticDigestEnabled,
        public readonly ?int $defaultListId,
        public readonly ?string $defaultLanguage,
        public readonly ?int $defaultSenderIdentityId,
        public readonly ?int $defaultTemplateId,
        public readonly ?int $maxSendRatePerMinute,
        public readonly ?array $rateLimit,
        public readonly ?array $editorialWorkflow,
        public readonly string $scope,
        public readonly string $scopeKey,
        public readonly bool $fromFallback,
    ) {}

    public static function fallback(): self
    {
        return new self(
            newsletterEnabled: false,
            doubleOptIn: true,
            requireConsent: true,
            sendEnabled: false,
            allowImport: false,
            aiGenerationEnabled: false,
            rssImportEnabled: false,
            wordpressApiImportEnabled: false,
            automaticDigestEnabled: false,
            defaultListId: null,
            defaultLanguage: null,
            defaultSenderIdentityId: null,
            defaultTemplateId: null,
            maxSendRatePerMinute: null,
            rateLimit: null,
            editorialWorkflow: null,
            scope: NewsletterSetting::SCOPE_GLOBAL,
            scopeKey: NewsletterSetting::SCOPE_GLOBAL,
            fromFallback: true,
        );
    }

    public static function fromSetting(NewsletterSetting $setting): self
    {
        return new self(
            newsletterEnabled: (bool) $setting->newsletter_enabled,
            doubleOptIn: (bool) $setting->double_opt_in,
            requireConsent: (bool) $setting->require_consent,
            sendEnabled: (bool) $setting->send_enabled,
            allowImport: (bool) $setting->allow_import,
            aiGenerationEnabled: (bool) $setting->ai_generation_enabled,
            rssImportEnabled: (bool) $setting->rss_import_enabled,
            wordpressApiImportEnabled: (bool) $setting->wordpress_api_import_enabled,
            automaticDigestEnabled: (bool) $setting->automatic_digest_enabled,
            defaultListId: $setting->default_list_id,
            defaultLanguage: $setting->default_language,
            defaultSenderIdentityId: $setting->default_sender_identity_id,
            defaultTemplateId: $setting->default_template_id,
            maxSendRatePerMinute: $setting->max_send_rate_per_minute,
            rateLimit: $setting->rate_limit_json,
            editorialWorkflow: $setting->editorial_workflow_json,
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
            'enabled' => $this->newsletterEnabled,
            'newsletter_enabled' => $this->newsletterEnabled,
            'double_opt_in' => $this->doubleOptIn,
            'require_consent' => $this->requireConsent,
            'send_enabled' => $this->sendEnabled,
            'allow_import' => $this->allowImport,
            'ai_generation_enabled' => $this->aiGenerationEnabled,
            'rss_import_enabled' => $this->rssImportEnabled,
            'wordpress_api_import_enabled' => $this->wordpressApiImportEnabled,
            'automatic_digest_enabled' => $this->automaticDigestEnabled,
            'default_list_id' => $this->defaultListId,
            'default_language' => $this->defaultLanguage,
            'default_sender_identity_id' => $this->defaultSenderIdentityId,
            'default_template_id' => $this->defaultTemplateId,
            'scope' => $this->scope,
            'scope_key' => $this->scopeKey,
            'from_fallback' => $this->fromFallback,
        ];
    }
}
