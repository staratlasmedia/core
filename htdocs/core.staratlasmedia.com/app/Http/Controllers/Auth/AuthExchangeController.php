<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\BridgeInstallation;
use App\Services\Auth\AuthorizationCodeService;
use App\Services\Auth\IdentityPayloadBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthExchangeController extends Controller
{
    public function __construct(
        private readonly AuthorizationCodeService $codes,
        private readonly IdentityPayloadBuilder $payloads,
    ) {}

    public function exchange(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string'],
            'state' => ['nullable', 'string'],
        ]);

        /** @var BridgeInstallation|null $installation */
        $installation = $request->attributes->get('bridge_installation');

        if ($installation === null) {
            return response()->json(['message' => 'Bridge authentication required.'], 401);
        }

        $record = $this->codes->consume($data['code'], $installation);

        if ($record === null) {
            return response()->json([
                'status' => 'invalid_code',
                'message' => 'Authorization code is invalid, expired, or already consumed.',
            ], 422);
        }

        $response = response()->json([
            'status' => 'ok',
            'payload' => $this->payloads->forAuthorizationCode($record),
        ]);

        $response->headers->set('Cache-Control', 'no-store');

        return $response;
    }
}
