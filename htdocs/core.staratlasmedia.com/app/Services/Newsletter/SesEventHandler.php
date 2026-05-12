<?php

namespace App\Services\Newsletter;

use App\Models\NewsletterDeliveryLog;
use App\Models\NewsletterEvent;
use App\Models\SnsWebhookEvent;

class SesEventHandler
{
    public function __construct(private readonly SuppressionService $suppression) {}

    /**
     * @param array<string, mixed> $snsMessage
     */
    public function handle(SnsWebhookEvent $webhookEvent, array $snsMessage): void
    {
        if ($webhookEvent->status === 'processed') {
            return;
        }

        if (($snsMessage['Type'] ?? null) !== 'Notification') {
            $webhookEvent->update([
                'status' => 'processed',
                'processed_at' => now(),
                'metadata_json' => array_merge($webhookEvent->metadata_json ?? [], [
                    'subscription_confirmation_url_present' => ($snsMessage['Type'] ?? null) === 'SubscriptionConfirmation' && ! empty($snsMessage['SubscribeURL']),
                    'unsubscribe_confirmation' => ($snsMessage['Type'] ?? null) === 'UnsubscribeConfirmation',
                    'auto_confirmed' => false,
                ]),
            ]);

            return;
        }

        $payload = json_decode((string) ($snsMessage['Message'] ?? '{}'), true) ?: [];
        $eventType = strtolower((string) ($payload['eventType'] ?? $payload['notificationType'] ?? 'unknown'));
        $mail = $payload['mail'] ?? [];
        $sesMessageId = $mail['messageId'] ?? null;
        $delivery = $sesMessageId ? NewsletterDeliveryLog::query()->where('ses_message_id', $sesMessageId)->orWhere('provider_message_id', $sesMessageId)->first() : null;

        if ($delivery instanceof NewsletterDeliveryLog) {
            $this->updateDelivery($delivery, $eventType, $payload);
        }

        if (in_array($eventType, ['bounce', 'complaint'], true)) {
            $this->suppressRecipients($payload, $eventType);
        }

        if ($delivery instanceof NewsletterDeliveryLog && $delivery->newsletterSubscriber !== null) {
            NewsletterEvent::query()->firstOrCreate(
                [
                    'provider' => 'ses',
                    'provider_message_id' => $sesMessageId,
                    'event_type' => $eventType,
                ],
                [
                    'site_id' => $delivery->newsletterSubscriber->site_id,
                    'newsletter_subscriber_id' => $delivery->newsletter_subscriber_id,
                    'newsletter_list_id' => $delivery->newsletter_list_id,
                    'metadata' => ['sns_message_id' => $snsMessage['MessageId'] ?? null],
                ],
            );
        }

        $webhookEvent->update(['status' => 'processed', 'processed_at' => now()]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function updateDelivery(NewsletterDeliveryLog $delivery, string $eventType, array $payload): void
    {
        match ($eventType) {
            'delivery' => $delivery->update(['status' => 'delivered', 'delivered_at' => now()]),
            'bounce' => $delivery->update(['status' => 'bounced', 'bounced_at' => now()]),
            'complaint' => $delivery->update(['status' => 'complained', 'complained_at' => now()]),
            'open' => $delivery->update([
                'status' => 'opened',
                'opened_at' => now(),
                'first_opened_at' => $delivery->first_opened_at ?: now(),
                'last_opened_at' => now(),
                'open_count' => $delivery->open_count + 1,
            ]),
            'click' => $delivery->update([
                'status' => 'clicked',
                'clicked_at' => now(),
                'first_clicked_at' => $delivery->first_clicked_at ?: now(),
                'last_clicked_at' => now(),
                'click_count' => $delivery->click_count + 1,
            ]),
            'reject' => $delivery->update(['status' => 'rejected', 'failed_at' => now(), 'failure_reason' => 'ses_reject']),
            default => null,
        };
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function suppressRecipients(array $payload, string $eventType): void
    {
        $recipients = $payload[$eventType]['bouncedRecipients'] ?? $payload[$eventType]['complainedRecipients'] ?? [];
        foreach ($recipients as $recipient) {
            $email = strtolower(trim((string) ($recipient['emailAddress'] ?? '')));
            if ($email !== '') {
                $this->suppression->suppress(hash('sha256', $email), $eventType === 'complaint' ? 'complaint' : 'hard_bounce', source: 'sns');
            }
        }
    }
}
