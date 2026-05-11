<?php

declare(strict_types=1);

namespace StarAtlas\CoreBridge\Manifest;

use StarAtlas\CoreBridge\Utils\Options;
use StarAtlas\CoreBridge\Utils\UrlResolver;

final class ManifestController
{
    public function __construct(
        private readonly Options $options,
        private readonly UrlResolver $resolver,
    ) {}

    public function serve(): void
    {
        nocache_headers();
        header('Content-Type: application/manifest+json; charset=utf-8');
        header('Cache-Control: no-cache, max-age=0');

        echo wp_json_encode($this->manifest(), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        exit;
    }

    public function pwaStart(): void
    {
        nocache_headers();

        $config = $this->options->config();
        $app = isset($_GET['app']) ? sanitize_key((string) wp_unslash($_GET['app'])) : '';
        $entry = isset($_COOKIE['core_pwa_entry']) ? sanitize_text_field((string) wp_unslash($_COOKIE['core_pwa_entry'])) : '';
        $redirects = is_array($config['pwa_start_redirects'] ?? null) ? $config['pwa_start_redirects'] : [];
        $target = '';

        if ($entry !== '' && isset($redirects[$entry]) && is_string($redirects[$entry])) {
            $target = $redirects[$entry];
        } elseif ($app !== '' && isset($redirects[$app]) && is_string($redirects[$app])) {
            $target = $redirects[$app];
        } elseif (! empty($config['pwa_default_start_url']) && is_string($config['pwa_default_start_url'])) {
            $target = $config['pwa_default_start_url'];
        }

        wp_safe_redirect($this->targetUrl($target));
        exit;
    }

    /**
     * @return array<string, mixed>
     */
    private function manifest(): array
    {
        $config = $this->options->config();

        if (isset($config['manifest']) && is_array($config['manifest'])) {
            return $config['manifest'];
        }

        $name = (string) ($config['manifest_name'] ?? get_bloginfo('name'));
        $scope = (string) ($config['registration_service_worker_scope'] ?? $this->resolver->configuredBasePath());

        return [
            'name' => $name,
            'short_name' => (string) ($config['manifest_short_name'] ?? $name),
            'id' => (string) ($config['manifest_id'] ?? $this->resolver->configuredBasePath()),
            'start_url' => (string) ($config['pwa_start_url'] ?? $this->resolver->configuredBasePath()),
            'scope' => $scope,
            'display' => (string) ($config['manifest_display'] ?? 'standalone'),
            'background_color' => (string) ($config['manifest_background_color'] ?? '#ffffff'),
            'theme_color' => (string) ($config['manifest_theme_color'] ?? '#111111'),
            'icons' => is_array($config['manifest_icons'] ?? null) ? $config['manifest_icons'] : [],
        ];
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
