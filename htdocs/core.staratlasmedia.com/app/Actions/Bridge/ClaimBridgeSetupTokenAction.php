<?php

namespace App\Actions\Bridge;

use App\Models\BridgeInstallation;
use App\Models\BridgeSetupToken;
use App\Services\Bridge\BridgeConfigBuilder;
use App\Services\Bridge\BridgeSecretFactory;
use App\Services\Bridge\BridgeTokenFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ClaimBridgeSetupTokenAction
{
    public function __construct(
        private readonly BridgeTokenFactory $tokenFactory,
        private readonly BridgeSecretFactory $secretFactory,
        private readonly BridgeConfigBuilder $configBuilder,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function execute(array $payload): array
    {
        return DB::transaction(function () use ($payload): array {
            $token = BridgeSetupToken::query()
                ->where('token_hash', $this->tokenFactory->hash((string) $payload['setup_token']))
                ->lockForUpdate()
                ->first();

            if (! $token instanceof BridgeSetupToken || ! $token->isClaimable()) {
                throw ValidationException::withMessages([
                    'setup_token' => 'Setup token is invalid, expired, revoked, or already consumed.',
                ]);
            }

            $detectedOrigin = $this->normalizeOrigin((string) $payload['detected_origin']);
            $detectedBasePath = $this->normalizeBasePath((string) $payload['detected_base_path']);

            $this->assertOriginAllowed($token, $detectedOrigin);
            $this->assertBasePathAllowed($token, $detectedBasePath);

            $secret = $this->secretFactory->make();
            $pushGroup = $token->pushGroup;

            $installation = BridgeInstallation::query()->create([
                'uuid' => (string) Str::uuid(),
                'site_id' => $token->site_id,
                'push_group_id' => $token->push_group_id,
                'site_origin_id' => $token->site_origin_id,
                'setup_token_id' => $token->id,
                'site_code' => $token->intended_site_code,
                'push_group_code' => $token->intended_push_group_code ?? $pushGroup?->code,
                'language' => $token->intended_language,
                'section' => $token->intended_section,
                'origin' => $detectedOrigin,
                'wp_home_url' => (string) $payload['wp_home_url'],
                'wp_site_url' => $payload['wp_site_url'] ?? null,
                'detected_base_path' => $detectedBasePath,
                'plugin_version' => $payload['plugin_version'] ?? null,
                'wordpress_version' => $payload['wordpress_version'] ?? null,
                'php_version' => $payload['php_version'] ?? null,
                'status' => 'active',
                'bridge_secret_encrypted' => $secret['secret'],
                'bridge_secret_fingerprint' => $secret['fingerprint'],
                'last_seen_at' => now(),
                'last_config_sync_at' => now(),
            ]);

            $token->forceFill([
                'status' => 'consumed',
                'consumed_at' => now(),
                'consumed_by_installation_id' => $installation->id,
            ])->save();

            return [
                'bridge_installation_id' => $installation->uuid,
                'bridge_secret' => $secret['secret'],
                'bridge_secret_fingerprint' => $secret['fingerprint'],
                'config' => $this->configBuilder->forInstallation($installation->fresh(['site', 'pushGroup', 'siteOrigin'])),
            ];
        });
    }

    private function assertOriginAllowed(BridgeSetupToken $token, string $origin): void
    {
        $allowedOrigin = $token->intended_origin ?? $token->siteOrigin?->origin ?? $token->site?->canonical_origin;

        if ($allowedOrigin !== null && $this->normalizeOrigin($allowedOrigin) !== $origin) {
            throw ValidationException::withMessages([
                'detected_origin' => 'Detected origin does not match this setup token.',
            ]);
        }
    }

    private function assertBasePathAllowed(BridgeSetupToken $token, string $basePath): void
    {
        $allowedPath = $token->intended_base_path ?? $token->siteOrigin?->path_prefix;

        if ($allowedPath !== null && $this->normalizeBasePath($allowedPath) !== $basePath) {
            throw ValidationException::withMessages([
                'detected_base_path' => 'Detected base path does not match this setup token.',
            ]);
        }
    }

    private function normalizeOrigin(string $origin): string
    {
        $parts = parse_url(trim($origin));

        if (! is_array($parts) || empty($parts['host'])) {
            return rtrim($origin, '/');
        }

        $scheme = $parts['scheme'] ?? 'https';
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';

        return $scheme.'://'.$parts['host'].$port;
    }

    private function normalizeBasePath(string $path): string
    {
        $path = '/'.trim($path, '/');

        return $path === '/' ? '/' : $path.'/';
    }
}
