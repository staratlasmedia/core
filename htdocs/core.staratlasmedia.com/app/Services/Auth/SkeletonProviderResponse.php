<?php

namespace App\Services\Auth;

use Illuminate\Http\JsonResponse;

class SkeletonProviderResponse
{
    public function disabled(string $provider, string $message = 'Authentication provider is disabled or not available.'): JsonResponse
    {
        return response()->json([
            'status' => 'not_available',
            'provider' => $provider,
            'message' => $message,
            'phase' => 'phase_7_skeleton',
        ], 503);
    }
}
