<?php

declare(strict_types=1);

namespace StarAtlas\CoreBridge\Push;

use StarAtlas\CoreBridge\Api\CoreClient;
use StarAtlas\CoreBridge\Utils\Options;

final class PushClickController
{
    public function __construct(
        private readonly Options $options,
        private readonly CoreClient $client,
    ) {}

    public function redirect(string $token): void
    {
        nocache_headers();

        $masked = $this->mask($token);
        do_action('star_atlas_core_bridge_push_click_received', $masked);

        // TODO: call Core with the click token using HMAC, receive a validated destination, then redirect there.
        $config = $this->options->config();
        $fallback = is_string($config['push_click_fallback_url'] ?? null) ? $config['push_click_fallback_url'] : '/';

        wp_safe_redirect($this->targetUrl($fallback));
        exit;
    }

    private function mask(string $token): string
    {
        if (strlen($token) <= 10) {
            return '***';
        }

        return substr($token, 0, 4).'...'.substr($token, -4);
    }

    private function targetUrl(string $target): string
    {
        if ($target === '') {
            return home_url('/');
        }

        if (str_starts_with($target, 'http://') || str_starts_with($target, 'https://')) {
            return wp_validate_redirect($target, home_url('/'));
        }

        return home_url('/'.ltrim($target, '/'));
    }
}
