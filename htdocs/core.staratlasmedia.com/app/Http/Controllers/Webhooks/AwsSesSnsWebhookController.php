<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\SnsWebhookEvent;
use App\Services\Newsletter\SesEventHandler;
use App\Services\Newsletter\SnsMessageVerifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AwsSesSnsWebhookController extends Controller
{
    public function __invoke(Request $request, SnsMessageVerifier $verifier, SesEventHandler $handler): JsonResponse
    {
        $payload = $request->json()->all();
        $payloadHash = hash('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '');
        $messageId = $payload['MessageId'] ?? null;
        $attributes = [
                'uuid' => (string) Str::uuid(),
                'sns_type' => $payload['Type'] ?? null,
                'topic_arn' => $payload['TopicArn'] ?? null,
                'signature_version' => $payload['SignatureVersion'] ?? null,
                'signing_cert_url' => $payload['SigningCertURL'] ?? null,
                'signature_hash' => isset($payload['Signature']) ? hash('sha256', (string) $payload['Signature']) : null,
                'payload_hash' => $payloadHash,
                'raw_payload_json' => $payload,
                'status' => 'received',
                'received_at' => now(),
        ];
        $event = $messageId
            ? SnsWebhookEvent::query()->firstOrCreate(['sns_message_id' => $messageId], $attributes)
            : SnsWebhookEvent::query()->create(['sns_message_id' => null] + $attributes);

        if ($event->status === 'processed') {
            return response()->json(['status' => 'duplicate']);
        }

        if (! $verifier->verify($payload, config('core.newsletter.sns_topic_arn'))) {
            $event->update(['status' => 'failed', 'failure_reason' => 'invalid_sns_signature']);

            return response()->json(['status' => 'invalid_signature'], 403);
        }

        $event->update(['status' => 'verified', 'verified_at' => now()]);
        $handler->handle($event, $payload);

        return response()->json(['status' => 'ok']);
    }
}
