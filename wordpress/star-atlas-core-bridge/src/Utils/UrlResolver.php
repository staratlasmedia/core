<?php

declare(strict_types=1);

namespace StarAtlas\CoreBridge\Utils;

final class UrlResolver
{
    public function __construct(private readonly Options $options) {}

    public function homeUrl(): string
    {
        return home_url('/');
    }

    public function siteUrl(): string
    {
        return site_url('/');
    }

    public function detectedOrigin(): string
    {
        $parts = wp_parse_url($this->homeUrl());

        if (! is_array($parts) || empty($parts['host'])) {
            return rtrim($this->homeUrl(), '/');
        }

        $scheme = $parts['scheme'] ?? 'https';
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';

        return $scheme.'://'.$parts['host'].$port;
    }

    public function detectedBasePath(): string
    {
        $parts = wp_parse_url($this->homeUrl());
        $path = is_array($parts) && isset($parts['path']) ? (string) $parts['path'] : '/';

        return $this->normalizeBasePath($path);
    }

    public function configuredBasePath(): string
    {
        return $this->normalizeBasePath((string) $this->options->configValue('wp_base_path', $this->detectedBasePath()));
    }

    public function requestPath(): string
    {
        $path = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '/';
        $parsed = wp_parse_url($path, PHP_URL_PATH);

        return $this->normalizeAbsolutePath(is_string($parsed) ? $parsed : '/');
    }

    public function normalizeBasePath(string $path): string
    {
        $path = '/'.trim($path, '/');

        return $path === '/' ? '/' : $path.'/';
    }

    public function normalizeAbsolutePath(string $path): string
    {
        $path = '/'.ltrim($path, '/');

        return rtrim($path, '/') === '' ? '/' : rtrim($path, '/');
    }

    public function pathWithBase(string $path): string
    {
        $base = $this->configuredBasePath();
        $path = '/'.ltrim($path, '/');

        if ($base === '/') {
            return $this->normalizeAbsolutePath($path);
        }

        return $this->normalizeAbsolutePath(rtrim($base, '/').$path);
    }

    public function pathMatches(string $requestPath, string $configuredPath): bool
    {
        $configured = $this->normalizeAbsolutePath($configuredPath);
        $withBase = $this->pathWithBase($configured);

        return in_array($this->normalizeAbsolutePath($requestPath), array_unique([$configured, $withBase]), true);
    }

    public function absoluteCoreUrl(string $pathOrUrl): string
    {
        if (str_starts_with($pathOrUrl, 'http://') || str_starts_with($pathOrUrl, 'https://')) {
            return $pathOrUrl;
        }

        return rtrim($this->coreBaseUrl(), '/').'/'.ltrim($pathOrUrl, '/');
    }

    public function coreBaseUrl(): string
    {
        $base = (string) $this->options->configValue('core_api_base', STAR_ATLAS_CORE_BRIDGE_CORE_URL);
        $base = apply_filters('star_atlas_core_bridge_core_url', $base);

        return rtrim((string) $base, '/');
    }
}
