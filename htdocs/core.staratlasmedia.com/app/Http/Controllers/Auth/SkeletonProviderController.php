<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthProviderResolver;
use App\Services\Auth\SkeletonProviderResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SkeletonProviderController extends Controller
{
    public function __construct(
        private readonly AuthProviderResolver $providers,
        private readonly SkeletonProviderResponse $responses,
    ) {}

    public function passkeyRegisterOptions(): JsonResponse
    {
        return $this->providerSkeleton('passkey');
    }

    public function passkeyRegisterVerify(): JsonResponse
    {
        return $this->providerSkeleton('passkey');
    }

    public function passkeyLoginOptions(): JsonResponse
    {
        return $this->providerSkeleton('passkey');
    }

    public function passkeyLoginVerify(): JsonResponse
    {
        return $this->providerSkeleton('passkey');
    }

    public function magicLinkRequest(): JsonResponse
    {
        return $this->providerSkeleton('magic_link');
    }

    public function magicLinkVerify(): JsonResponse
    {
        return $this->providerSkeleton('magic_link');
    }

    public function passwordLogin(): JsonResponse
    {
        return $this->providerSkeleton('password');
    }

    public function passwordRegister(): JsonResponse
    {
        return $this->providerSkeleton('password');
    }

    public function passwordForgot(): JsonResponse
    {
        return $this->providerSkeleton('password');
    }

    public function passwordReset(): JsonResponse
    {
        return $this->providerSkeleton('password');
    }

    public function oauthRedirect(string $provider): JsonResponse
    {
        return $this->providerSkeleton($provider);
    }

    public function oauthCallback(string $provider): JsonResponse
    {
        return $this->providerSkeleton($provider);
    }

    public function logout(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'accepted',
            'message' => 'Logout skeleton acknowledged; full global logout is not implemented in Phase 7.',
            'phase' => 'phase_7_skeleton',
        ]);
    }

    private function providerSkeleton(string $provider): JsonResponse
    {
        $record = $this->providers->publicEnabledProvider($provider);

        if ($record === null) {
            return $this->responses->disabled($provider);
        }

        return response()->json([
            'status' => 'skeleton_only',
            'provider' => $provider,
            'message' => 'Provider is enabled, but production authentication is not implemented in Phase 7.',
            'phase' => 'phase_7_skeleton',
        ], 501);
    }
}
