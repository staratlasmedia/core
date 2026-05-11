<?php

declare(strict_types=1);

namespace StarAtlas\CoreBridge\Setup;

use StarAtlas\CoreBridge\Api\CoreClient;
use StarAtlas\CoreBridge\Utils\Options;
use StarAtlas\CoreBridge\Utils\UrlResolver;

final class SetupService
{
    public function __construct(
        private readonly Options $options,
        private readonly UrlResolver $resolver,
        private readonly CoreClient $client,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function claim(string $setupToken): array
    {
        $response = $this->client->claimSetup([
            'setup_token' => $setupToken,
            'wp_home_url' => $this->resolver->homeUrl(),
            'wp_site_url' => $this->resolver->siteUrl(),
            'detected_origin' => $this->resolver->detectedOrigin(),
            'detected_base_path' => $this->resolver->detectedBasePath(),
            'wordpress_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'plugin_version' => STAR_ATLAS_CORE_BRIDGE_VERSION,
        ]);

        if (($response['ok'] ?? false) === true) {
            $this->options->update([
                'bridge_installation_id' => (string) ($response['bridge_installation_id'] ?? ''),
                'bridge_secret' => (string) ($response['bridge_secret'] ?? ''),
                'bridge_secret_fingerprint' => (string) ($response['bridge_secret_fingerprint'] ?? ''),
                'config' => is_array($response['config'] ?? null) ? $response['config'] : [],
                'last_config_sync' => gmdate('c'),
                'last_connection_check' => gmdate('c'),
                'connection_status' => 'configured',
            ]);
        }

        return $response;
    }

    /**
     * @return array<string, mixed>
     */
    public function refresh(): array
    {
        $response = $this->client->refreshConfig();

        if (($response['ok'] ?? false) === true && is_array($response['config'] ?? null)) {
            $this->options->update([
                'config' => $response['config'],
                'last_config_sync' => gmdate('c'),
                'connection_status' => 'configured',
            ]);
        }

        return $response;
    }
}
