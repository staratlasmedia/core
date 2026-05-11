<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExactOriginCors
{
    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->headers->get('Origin');
        $allowedOrigins = config('core.allowed_origins', []);

        if ($request->isMethod('OPTIONS')) {
            $response = response('', 204);
        } else {
            $response = $next($request);
        }

        $response->headers->set('Vary', 'Origin', false);

        if ($origin !== null && in_array($origin, $allowedOrigins, true)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, X-Core-Bridge-Id, X-Core-Client, X-Core-Nonce, X-Core-Signature, X-Core-Timestamp, X-Requested-With');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
            $response->headers->set('Access-Control-Max-Age', '600');
        }

        return $response;
    }
}
