<?php

namespace App\Services\LegacyPush;

class LegacyPushTokenParser
{
    /**
     * @return array{endpoint: string, p256dh: string|null, auth: string|null, endpoint_hash: string}|null
     */
    public function parse(?string $token): ?array
    {
        if ($token === null || trim($token) === '') {
            return null;
        }

        $payload = json_decode($token, true);

        if (! is_array($payload)) {
            return null;
        }

        $endpoint = $this->stringValue($payload['endpoint'] ?? null);
        $keys = is_array($payload['keys'] ?? null) ? $payload['keys'] : [];
        $p256dh = $this->stringValue($payload['p256dh'] ?? $keys['p256dh'] ?? null);
        $auth = $this->stringValue($payload['auth'] ?? $keys['auth'] ?? null);

        if ($endpoint === null || $p256dh === null || $auth === null) {
            return null;
        }

        return [
            'endpoint' => $endpoint,
            'p256dh' => $p256dh,
            'auth' => $auth,
            'endpoint_hash' => hash('sha256', $endpoint),
        ];
    }

    private function stringValue(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
