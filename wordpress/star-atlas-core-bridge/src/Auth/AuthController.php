<?php

declare(strict_types=1);

namespace StarAtlas\CoreBridge\Auth;

use StarAtlas\CoreBridge\Api\CoreClient;
use StarAtlas\CoreBridge\Utils\Options;

final class AuthController
{
    public function __construct(
        private readonly Options $options,
        private readonly CoreClient $client,
    ) {}

    public function callback(): void
    {
        nocache_headers();

        $state = isset($_GET['state']) ? sanitize_text_field((string) wp_unslash($_GET['state'])) : '';
        $code = isset($_GET['code']) ? sanitize_text_field((string) wp_unslash($_GET['code'])) : '';

        // Phase 7 will exchange this one-time code server-to-server and create a local first-party session.
        if ($state !== '' && $code !== '') {
            setcookie('star_atlas_core_auth_seen', '1', [
                'expires' => time() + 300,
                'path' => COOKIEPATH ?: '/',
                'secure' => is_ssl(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }

        wp_safe_redirect(home_url('/'));
        exit;
    }
}
