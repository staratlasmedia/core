<?php

namespace App\Services\Newsletter;

use App\Models\NewsletterList;
use App\Models\NewsletterSubscriber;
use App\Models\Site;
use App\Services\Audience\AudiencePreferenceService;
use App\Services\Audience\AudienceTopicResolver;
use Illuminate\Support\Str;

class NewsletterSubscriptionService
{
    public function __construct(
        private readonly NewsletterSettingsResolver $settingsResolver,
        private readonly EmailAddressHasher $hasher,
        private readonly SuppressionService $suppression,
        private readonly AudiencePreferenceService $preferences,
        private readonly AudienceTopicResolver $topics,
    ) {}

    /**
     * @param array<string, mixed> $payload
     */
    public function subscribe(array $payload): array
    {
        $site = Site::query()->where('code', $payload['site_code'])->firstOrFail();
        $settings = $this->settingsResolver->resolve($site->id, $site->push_group_id);

        if (! $settings->newsletterEnabled) {
            return ['status' => 'disabled', 'newsletter_enabled' => false];
        }

        if ($settings->requireConsent && empty($payload['consent_version'])) {
            return ['status' => 'consent_required'];
        }

        $email = (string) $payload['email'];
        $hash = $this->hasher->hash($email);
        $list = isset($payload['list_code'])
            ? NewsletterList::query()->where('code', $payload['list_code'])->first()
            : ($settings->defaultListId ? NewsletterList::query()->find($settings->defaultListId) : null);

        if ($this->suppression->isSuppressed($hash, $site->id, $list?->id)) {
            return ['status' => 'suppressed'];
        }

        $status = $settings->doubleOptIn ? 'pending' : 'subscribed';
        $subscriber = NewsletterSubscriber::query()->updateOrCreate(
            ['site_id' => $site->id, 'normalized_email_hash' => $hash],
            [
                'uuid' => (string) Str::uuid(),
                'push_group_id' => $site->push_group_id,
                'email_hash' => $hash,
                'email_encrypted' => $email,
                'status' => $status,
                'language' => $payload['language'] ?? $settings->defaultLanguage,
                'source_url' => $payload['source_url'] ?? null,
                'source_url_hash' => isset($payload['source_url']) ? hash('sha256', (string) $payload['source_url']) : null,
                'source_type' => 'subscribe_api',
                'consent_version' => $payload['consent_version'] ?? null,
                'consent_ip_hash' => ! empty($payload['ip']) ? hash('sha256', (string) $payload['ip']) : null,
                'consent_user_agent_hash' => ! empty($payload['user_agent']) ? hash('sha256', (string) $payload['user_agent']) : null,
                'consented_at' => now(),
                'confirmed_at' => $settings->doubleOptIn ? null : now(),
                'subscribed_at' => $settings->doubleOptIn ? null : now(),
            ],
        );

        if ($list instanceof NewsletterList) {
            $subscriber->lists()->syncWithoutDetaching([
                $list->id => [
                    'status' => $status,
                    'subscribed_at' => $settings->doubleOptIn ? null : now(),
                    'source_url' => $payload['source_url'] ?? null,
                ],
            ]);
        }

        if (! empty($payload['topic_ids']) && is_array($payload['topic_ids'])) {
            $allowed = $this->topics->forChannel($site->id, $site->push_group_id, 'newsletter', true)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
            $topicIds = array_values(array_intersect(array_map('intval', $payload['topic_ids']), $allowed));

            if ($topicIds !== []) {
                $this->preferences->saveNewsletterPreferences($subscriber, $topicIds, 'explicit', $payload['source_url'] ?? null);
            }
        }

        return [
            'status' => $status,
            'subscriber_uuid' => $subscriber->uuid,
            'double_opt_in' => $settings->doubleOptIn,
        ];
    }
}
