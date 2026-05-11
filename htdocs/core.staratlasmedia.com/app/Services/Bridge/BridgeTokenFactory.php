<?php

namespace App\Services\Bridge;

use App\Models\BridgeSetupToken;
use App\Models\PushGroup;
use App\Models\Site;
use App\Models\SiteOrigin;
use Illuminate\Support\Str;

class BridgeTokenFactory
{
    /**
     * @param  array<string, mixed>  $metadata
     * @return array{token: string, record: BridgeSetupToken}
     */
    public function create(
        Site $site,
        ?PushGroup $pushGroup = null,
        ?SiteOrigin $siteOrigin = null,
        ?string $section = null,
        ?int $createdBy = null,
        array $metadata = [],
    ): array {
        $rawToken = 'sacb_'.Str::random(48);
        $pushGroup ??= $site->pushGroup;
        $siteOrigin ??= $site->origins()->where('is_primary', true)->first();

        $record = BridgeSetupToken::query()->create([
            'uuid' => (string) Str::uuid(),
            'token_hash' => $this->hash($rawToken),
            'site_id' => $site->id,
            'push_group_id' => $pushGroup?->id,
            'site_origin_id' => $siteOrigin?->id,
            'intended_site_code' => $site->code,
            'intended_push_group_code' => $pushGroup?->code ?? $site->push_group,
            'intended_language' => $site->language ?? $pushGroup?->language,
            'intended_section' => $section ?? $this->sectionFromPath($siteOrigin?->path_prefix),
            'intended_origin' => $siteOrigin?->origin ?? $site->canonical_origin,
            'intended_base_path' => $this->normalizeBasePath($siteOrigin?->path_prefix ?? '/'),
            'status' => 'active',
            'expires_at' => now()->addMinutes((int) config('core.bridge.setup_token_ttl_minutes', 60)),
            'created_by' => $createdBy,
            'metadata_json' => $metadata ?: null,
        ]);

        return ['token' => $rawToken, 'record' => $record];
    }

    public function hash(string $token): string
    {
        return hash('sha256', $token);
    }

    private function sectionFromPath(?string $path): ?string
    {
        return match ($this->normalizeBasePath($path ?? '/')) {
            '/automobili/' => 'automobili',
            '/en/' => 'en',
            default => 'main',
        };
    }

    private function normalizeBasePath(string $path): string
    {
        $path = '/'.trim($path, '/');

        return $path === '/' ? '/' : $path.'/';
    }
}
