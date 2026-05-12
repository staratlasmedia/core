<?php

namespace App\Services\Newsletter;

use App\Models\EmailSenderIdentity;
use Illuminate\Support\Facades\Mail;
use Throwable;

class NewsletterSesTestService
{
    public function __construct(private readonly NewsletterOperationalGate $gate) {}

    /**
     * @return array<string, mixed>
     */
    public function sendControlledTest(EmailSenderIdentity $identity, string $recipient): array
    {
        $blockedReason = $this->gate->checkControlledTestSend($identity);

        if ($blockedReason !== null) {
            return $this->record($identity, [
                'status' => 'blocked',
                'reason' => $blockedReason,
                'recipient_hash' => hash('sha256', strtolower(trim($recipient))),
            ]);
        }

        if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return $this->record($identity, [
                'status' => 'failed',
                'reason' => 'invalid_test_recipient',
            ]);
        }

        try {
            Mail::mailer('ses')->raw('Star Atlas Core controlled newsletter test email.', function ($message) use ($identity, $recipient): void {
                $message->to($recipient)
                    ->from($identity->from_email, $identity->from_name)
                    ->replyTo($identity->reply_to ?: $identity->from_email)
                    ->subject('Star Atlas Core newsletter test');
            });
        } catch (Throwable $exception) {
            return $this->record($identity, [
                'status' => 'failed',
                'reason' => 'mail_transport_failed',
                'message' => str($exception->getMessage())->limit(180)->toString(),
                'recipient_hash' => hash('sha256', strtolower(trim($recipient))),
            ]);
        }

        return $this->record($identity, [
            'status' => 'sent',
            'recipient_hash' => hash('sha256', strtolower(trim($recipient))),
            'sent_at' => now()->toISOString(),
        ]);
    }

    /**
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    private function record(EmailSenderIdentity $identity, array $result): array
    {
        $identity->update([
            'last_test_result_json' => $result,
            'last_tested_at' => now(),
        ]);

        return $result;
    }
}
