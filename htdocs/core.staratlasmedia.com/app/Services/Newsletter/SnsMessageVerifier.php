<?php

namespace App\Services\Newsletter;

use Illuminate\Support\Facades\Http;
use Throwable;

class SnsMessageVerifier
{
    /**
     * @param array<string, mixed> $message
     */
    public function verify(array $message, ?string $expectedTopicArn = null): bool
    {
        try {
            if ($expectedTopicArn !== null && ($message['TopicArn'] ?? null) !== $expectedTopicArn) {
                return false;
            }

            $certUrl = (string) ($message['SigningCertURL'] ?? '');
            $host = parse_url($certUrl, PHP_URL_HOST);
            $scheme = parse_url($certUrl, PHP_URL_SCHEME);

            if ($scheme !== 'https' || ! is_string($host) || ! preg_match('/^sns\\.[a-z0-9-]+\\.amazonaws\\.com(\\.cn)?$/', $host)) {
                return false;
            }

            $signature = base64_decode((string) ($message['Signature'] ?? ''), true);
            if ($signature === false) {
                return false;
            }

            $certificate = Http::timeout(10)->get($certUrl);
            if (! $certificate->successful()) {
                return false;
            }

            $publicKey = openssl_pkey_get_public($certificate->body());
            if ($publicKey === false) {
                return false;
            }

            $algorithm = ($message['SignatureVersion'] ?? '1') === '2' ? OPENSSL_ALGO_SHA256 : OPENSSL_ALGO_SHA1;

            return openssl_verify($this->stringToSign($message), $signature, $publicKey, $algorithm) === 1;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @param array<string, mixed> $message
     */
    private function stringToSign(array $message): string
    {
        $type = $message['Type'] ?? '';
        $fields = $type === 'Notification'
            ? ['Message', 'MessageId', 'Subject', 'Timestamp', 'TopicArn', 'Type']
            : ['Message', 'MessageId', 'SubscribeURL', 'Timestamp', 'Token', 'TopicArn', 'Type'];

        $parts = [];
        foreach ($fields as $field) {
            if (array_key_exists($field, $message) && $message[$field] !== null && $message[$field] !== '') {
                $parts[] = $field;
                $parts[] = (string) $message[$field];
            }
        }

        return implode("\n", $parts);
    }
}
