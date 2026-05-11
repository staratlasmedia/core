<?php

namespace App\Http\Middleware;

use App\Services\Bridge\BridgeHmacVerifier;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyBridgeHmac
{
    public function __construct(private readonly BridgeHmacVerifier $verifier) {}

    public function handle(Request $request, Closure $next): Response
    {
        $installation = $this->verifier->installationFromRequest($request);

        if ($installation === null || ! $this->verifier->verify($request, $installation)) {
            return response()->json([
                'message' => 'Invalid bridge signature.',
            ], 401);
        }

        $request->attributes->set('bridge_installation', $installation);

        return $next($request);
    }
}
