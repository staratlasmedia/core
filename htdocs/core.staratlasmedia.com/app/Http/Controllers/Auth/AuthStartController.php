<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\BridgeInstallation;
use App\Models\Site;
use App\Services\Auth\AuthProviderResolver;
use App\Services\Auth\BridgeCallbackUrlResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthStartController extends Controller
{
    public function __construct(
        private readonly AuthProviderResolver $providers,
        private readonly BridgeCallbackUrlResolver $callbacks,
    ) {}

    public function start(Request $request): JsonResponse
    {
        $data = $request->validate([
            'bridge_installation_id' => ['nullable', 'string'],
            'site_code' => ['nullable', 'string'],
            'redirect_uri' => ['nullable', 'url'],
            'state' => ['required', 'string'],
            'nonce' => ['nullable', 'string'],
            'mode' => ['nullable', 'in:popup,redirect'],
        ]);

        $bridge = $this->bridgeInstallation($data['bridge_installation_id'] ?? null);
        $site = $bridge?->site ?? $this->site($data['site_code'] ?? null);
        $redirectUri = $data['redirect_uri'] ?? ($bridge ? $this->callbacks->callbackUrl($bridge) : null);

        if ($site === null || $redirectUri === null || ! $this->redirectAllowed($redirectUri, $bridge, $site)) {
            return response()->json([
                'status' => 'invalid_request',
                'message' => 'Invalid site or redirect URI.',
            ], 422);
        }

        $providers = $this->providers->publicEnabledProviders($site, $bridge?->pushGroup, $bridge);

        if ($providers->isEmpty()) {
            return response()->json([
                'status' => 'not_available',
                'message' => 'No public authentication providers are enabled.',
                'providers' => [],
                'phase' => 'phase_7_skeleton',
            ], 503);
        }

        return response()->json([
            'status' => 'available',
            'mode' => $data['mode'] ?? 'popup',
            'providers' => $providers->pluck('code')->values(),
        ]);
    }

    public function popup(Request $request): JsonResponse
    {
        $providers = $this->providers->publicEnabledProviders();

        if ($providers->isEmpty()) {
            return response()->json([
                'status' => 'not_available',
                'message' => 'Login popup skeleton is present, but no public providers are enabled.',
                'providers' => [],
                'phase' => 'phase_7_skeleton',
            ], 503);
        }

        return response()->json([
            'status' => 'available',
            'providers' => $providers->pluck('code')->values(),
        ]);
    }

    public function silentCheck(Request $request): JsonResponse
    {
        $response = response()->json([
            'type' => 'staratlas.core.auth',
            'status' => 'anonymous',
            'message' => 'Silent check skeleton only; no one-time code is issued without an active Core session.',
            'nonce' => $request->query('nonce'),
            'phase' => 'phase_7_skeleton',
        ]);

        $response->headers->set('Cache-Control', 'no-store');

        return $response;
    }

    private function bridgeInstallation(?string $uuid): ?BridgeInstallation
    {
        if ($uuid === null || $uuid === '') {
            return null;
        }

        return BridgeInstallation::query()
            ->where('uuid', $uuid)
            ->where('status', 'active')
            ->first();
    }

    private function site(?string $code): ?Site
    {
        if ($code === null || $code === '') {
            return null;
        }

        return Site::query()->where('code', $code)->where('status', 'active')->first();
    }

    private function redirectAllowed(string $redirectUri, ?BridgeInstallation $bridge, Site $site): bool
    {
        if ($bridge !== null) {
            return $redirectUri === $this->callbacks->callbackUrl($bridge);
        }

        $origin = parse_url($redirectUri, PHP_URL_SCHEME).'://'.parse_url($redirectUri, PHP_URL_HOST);

        return $origin === $site->canonical_origin;
    }
}
