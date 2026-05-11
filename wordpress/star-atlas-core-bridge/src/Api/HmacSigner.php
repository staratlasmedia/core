<?php

declare(strict_types=1);

namespace StarAtlas\CoreBridge\Api;

final class HmacSigner
{
    /**
     * @param array<string, string> $headers
     * @return array<string, string>
     */
    public function sign(string $method, string $path, string $body, string $installationId, string $secret, array $headers = []): array
    {
        $timestamp = (string) time();
        $nonce = wp_generate_uuid4();
        $canonical = implode("\n", [
            strtoupper($method),
            '/'.ltrim($path, '/'),
            $timestamp,
            $nonce,
            hash('sha256', $body),
        ]);

        return array_merge($headers, [
            'X-Core-Bridge-Id' => $installationId,
            'X-Core-Timestamp' => $timestamp,
            'X-Core-Nonce' => $nonce,
            'X-Core-Signature' => hash_hmac('sha256', $canonical, $secret),
        ]);
    }
}
