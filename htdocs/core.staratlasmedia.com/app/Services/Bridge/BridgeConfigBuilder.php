<?php

namespace App\Services\Bridge;

use App\Models\BridgeInstallation;
use App\Models\PushGroup;
use App\Models\Site;
use App\Models\SiteOrigin;

class BridgeConfigBuilder
{
    /**
     * @return array<string, mixed>
     */
    public function forInstallation(BridgeInstallation $installation): array
    {
        return $this->build(
            site: $installation->site,
            pushGroup: $installation->pushGroup,
            siteOrigin: $installation->siteOrigin,
            origin: $installation->origin,
            basePath: $installation->detected_base_path,
            siteCode: $installation->site_code,
            pushGroupCode: $installation->push_group_code,
            language: $installation->language,
            section: $installation->section,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function previewForToken(\App\Models\BridgeSetupToken $token): array
    {
        return $this->build(
            site: $token->site,
            pushGroup: $token->pushGroup,
            siteOrigin: $token->siteOrigin,
            origin: $token->intended_origin ?? $token->site?->canonical_origin,
            basePath: $token->intended_base_path ?? $token->siteOrigin?->path_prefix ?? '/',
            siteCode: $token->intended_site_code,
            pushGroupCode: $token->intended_push_group_code,
            language: $token->intended_language,
            section: $token->intended_section,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function build(
        ?Site $site,
        ?PushGroup $pushGroup,
        ?SiteOrigin $siteOrigin,
        ?string $origin,
        ?string $basePath,
        ?string $siteCode,
        ?string $pushGroupCode,
        ?string $language,
        ?string $section,
    ): array {
        $basePath = $this->normalizeBasePath($basePath ?? $siteOrigin?->path_prefix ?? '/');
        $pushGroupCode ??= $pushGroup?->code ?? $site?->push_group;
        $siteCode ??= $site?->code;
        $language ??= $site?->language ?? $pushGroup?->language;
        $section ??= $this->sectionFromBasePath($basePath);

        $serviceWorkerUrl = $pushGroup?->service_worker_url ?: '/smart_sw.js';
        $serviceWorkerScope = $pushGroup?->service_worker_scope ?: '/';
        $manifestUrl = '/core-manifest.webmanifest?app='.$pushGroupCode;
        $pwaStartUrl = '/pwa-start/?app='.$pushGroupCode;

        if ($basePath === '/en/' || $section === 'en' || $language === 'en') {
            $serviceWorkerUrl = '/en/smart_sw.js';
            $serviceWorkerScope = '/en/';
            $manifestUrl = '/en/core-manifest.webmanifest?app='.$pushGroupCode;
            $pwaStartUrl = '/en/pwa-start/?app='.$pushGroupCode;
        } elseif ($basePath === '/automobili/') {
            $serviceWorkerUrl = '/smart_sw.js';
            $serviceWorkerScope = '/';
        }

        $config = [
            'core_api_base' => rtrim((string) config('core.bridge.api_base', config('app.url', 'https://core.staratlasmedia.com')), '/'),
            'site_code' => $siteCode,
            'push_group_code' => $pushGroupCode,
            'language' => $language,
            'section' => $section,
            'origin' => $origin ?? $site?->canonical_origin,
            'wp_base_path' => $basePath,
            'manifest_url' => $manifestUrl,
            'pwa_start_url' => $pwaStartUrl,
            'registration_service_worker_url' => $serviceWorkerUrl,
            'registration_service_worker_scope' => $serviceWorkerScope,
            'sdk_url' => (string) config('core.bridge.sdk_url', 'https://core.staratlasmedia.com/sdk/core-sdk.iife.js'),
        ];

        if ($basePath === '/automobili/') {
            $config['local_service_worker_paths'] = ['/automobili/smart_sw.js'];
        }

        return $config;
    }

    private function normalizeBasePath(string $path): string
    {
        $path = '/'.trim($path, '/');

        return $path === '/' ? '/' : $path.'/';
    }

    private function sectionFromBasePath(string $basePath): string
    {
        return match ($basePath) {
            '/automobili/' => 'automobili',
            '/en/' => 'en',
            default => 'main',
        };
    }
}
