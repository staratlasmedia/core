<?php

namespace App\Services\Audience;

use App\Models\AudiencePreferenceEvent;
use App\Models\NewsletterSubscriber;
use App\Models\NewsletterSubscriberTopicPreference;
use App\Models\PushSubscription;
use App\Models\PushSubscriptionTopicPreference;

class AudiencePreferenceService
{
    /**
     * @param array<int> $topicIds
     */
    public function saveNewsletterPreferences(NewsletterSubscriber $subscriber, array $topicIds, string $source = 'explicit', ?string $sourceUrl = null): void
    {
        foreach (array_unique($topicIds) as $topicId) {
            NewsletterSubscriberTopicPreference::query()->updateOrCreate(
                ['newsletter_subscriber_id' => $subscriber->id, 'audience_topic_id' => $topicId],
                ['status' => 'subscribed', 'source' => $source, 'source_url' => $sourceUrl, 'consented_at' => now()],
            );

            AudiencePreferenceEvent::query()->create([
                'newsletter_subscriber_id' => $subscriber->id,
                'audience_topic_id' => $topicId,
                'channel' => 'newsletter',
                'event_type' => 'subscribed',
                'new_status' => 'subscribed',
                'source' => $source,
                'source_url' => $sourceUrl,
            ]);
        }
    }

    /**
     * @param array<int> $topicIds
     */
    public function savePushPreferences(PushSubscription $subscription, array $topicIds, string $source = 'explicit', ?string $sourceUrl = null): void
    {
        foreach (array_unique($topicIds) as $topicId) {
            PushSubscriptionTopicPreference::query()->updateOrCreate(
                ['push_subscription_id' => $subscription->id, 'audience_topic_id' => $topicId],
                ['status' => 'subscribed', 'source' => $source, 'source_url' => $sourceUrl, 'consented_at' => now()],
            );
        }
    }
}
