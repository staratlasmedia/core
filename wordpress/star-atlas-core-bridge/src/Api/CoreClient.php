<?php

declare(strict_types=1);

namespace StarAtlas\CoreBridge\Api;

use StarAtlas\CoreBridge\Utils\Options;
use StarAtlas\CoreBridge\Utils\UrlResolver;
use WP_Error;

final class CoreClient
{
    private HmacSigner $signer;

    public function __construct(
        private readonly Options $options,
        private readonly UrlResolver $resolver,
    ) {
        $this->signer = new HmacSigner();
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function claimSetup(array $payload): array
    {
        return $this->request('POST', '/api/bridge/setup/claim', $payload, false);
    }

    /**
     * @return array<string, mixed>
     */
    public function refreshConfig(): array
    {
        return $this->request('GET', '/api/bridge/config', [], true);
    }

    /**
     * @return array<string, mixed>
     */
    public function heartbeat(): array
    {
        return $this->request('POST', '/api/bridge/heartbeat', [
            'plugin_version' => STAR_ATLAS_CORE_BRIDGE_VERSION,
            'wordpress_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'status' => 'active',
        ], true);
    }

    /**
     * @return array<string, mixed>
     */
    public function updateCheck(string $channel): array
    {
        return $this->request('GET', '/api/bridge/plugin/update-check?channel='.rawurlencode($channel), [], true);
    }

    /**
     * @return array<string, mixed>
     */
    public function pluginInfo(string $channel): array
    {
        return $this->request('GET', '/api/bridge/plugin/info?channel='.rawurlencode($channel), [], true);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function request(string $method, string $path, array $payload = [], bool $signed = true): array
    {
        $body = $method === 'GET' ? '' : (string) wp_json_encode($payload);
        $url = $this->resolver->absoluteCoreUrl($path);
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        if ($signed) {
            $installationId = $this->options->installationId();
            $secret = $this->options->secret();

            if ($installationId === '' || $secret === '') {
                return ['ok' => false, 'message' => 'Bridge credentials are not configured.'];
            }

            $parsedPath = wp_parse_url($url, PHP_URL_PATH);
            $headers = $this->signer->sign($method, is_string($parsedPath) ? $parsedPath : $path, $body, $installationId, $secret, $headers);
        }

        $response = wp_remote_request($url, [
            'method' => $method,
            'headers' => $headers,
            'body' => $body,
            'timeout' => 15,
        ]);

        if ($response instanceof WP_Error) {
            return ['ok' => false, 'message' => $response->get_error_message()];
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $decoded = json_decode((string) wp_remote_retrieve_body($response), true);
        $data = is_array($decoded) ? $decoded : [];
        $data['ok'] = $code >= 200 && $code < 300;
        $data['status_code'] = $code;

        return $data;
    }
}
