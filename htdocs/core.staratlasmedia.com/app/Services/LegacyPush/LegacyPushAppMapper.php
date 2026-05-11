<?php

namespace App\Services\LegacyPush;

class LegacyPushAppMapper
{
    /**
     * @param  array<string, mixed>  $platformSettings
     * @return array<string, mixed>|null
     */
    public function resolve(int $appid, array $platformSettings = []): ?array
    {
        $mapping = config("core.legacy_push.app_mappings.{$appid}");

        if (! is_array($mapping)) {
            return null;
        }

        $origin = $mapping['origin'] ?? null;

        if (! is_string($origin) || trim($origin) === '') {
            $origin = $this->originFromSettings($platformSettings);
        }

        if ($origin === null) {
            return null;
        }

        $mapping['legacy_appid'] = $appid;
        $mapping['origin'] = $this->normalizeOrigin($origin);
        $mapping['path_prefix'] = $this->normalizePath((string) ($mapping['path_prefix'] ?? '/'), true);
        $mapping['service_worker_url'] = $this->normalizePath((string) ($mapping['service_worker_url'] ?? '/smart_sw.js'), false);
        $mapping['service_worker_scope'] = $this->normalizePath((string) ($mapping['service_worker_scope'] ?? '/'), true);
        $mapping['full_service_worker_url'] = $mapping['origin'].$mapping['service_worker_url'];

        return $mapping;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function configuredMappings(): array
    {
        return config('core.legacy_push.app_mappings', []);
    }

    /**
     * @return array<int, string>
     */
    public function allowedPlatforms(): array
    {
        return config('core.legacy_push.allowed_platforms', []);
    }

    private function originFromSettings(array $settings): ?string
    {
        foreach (['siteurl', 'site_url', 'siteURL', 'url', 'origin', 'sitedomain'] as $key) {
            if (isset($settings[$key]) && is_string($settings[$key]) && trim($settings[$key]) !== '') {
                return $settings[$key];
            }
        }

        return null;
    }

    private function normalizeOrigin(string $origin): string
    {
        $origin = trim($origin);

        if (! str_starts_with($origin, 'http://') && ! str_starts_with($origin, 'https://')) {
            $origin = 'https://'.$origin;
        }

        $parts = parse_url($origin);

        if (! is_array($parts) || empty($parts['host'])) {
            return rtrim($origin, '/');
        }

        $scheme = $parts['scheme'] ?? 'https';
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';

        return $scheme.'://'.$parts['host'].$port;
    }

    private function normalizePath(string $path, bool $directory): string
    {
        $path = '/'.ltrim(trim($path), '/');

        if ($directory && ! str_ends_with($path, '/')) {
            return $path.'/';
        }

        if (! $directory && $path !== '/') {
            return rtrim($path, '/');
        }

        return $path;
    }
}
