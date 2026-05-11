<?php

namespace App\Services\Bridge;

use App\Models\BridgeInstallation;
use Illuminate\Http\Request;

class BridgeHmacVerifier
{
    public function installationFromRequest(Request $request): ?BridgeInstallation
    {
        $uuid = $request->header('X-Core-Bridge-Id');

        if (! is_string($uuid) || $uuid === '') {
            return null;
        }

        return BridgeInstallation::query()
            ->where('uuid', $uuid)
            ->where('status', 'active')
            ->first();
    }

    public function verify(Request $request, BridgeInstallation $installation): bool
    {
        $timestamp = $request->header('X-Core-Timestamp');
        $nonce = $request->header('X-Core-Nonce');
        $signature = $request->header('X-Core-Signature');

        if (! is_string($timestamp) || ! ctype_digit($timestamp) || ! is_string($nonce) || $nonce === '' || ! is_string($signature)) {
            return false;
        }

        $drift = abs(now()->timestamp - (int) $timestamp);

        if ($drift > (int) config('core.bridge.hmac_timestamp_tolerance_seconds', 300)) {
            return false;
        }

        foreach ($this->canonicalStrings($request, $timestamp, $nonce) as $canonical) {
            $expected = hash_hmac('sha256', $canonical, $installation->bridge_secret_encrypted);

            if (hash_equals($expected, $signature)) {
                return true;
            }
        }

        return false;
    }

    public function canonicalString(Request $request, string $timestamp, string $nonce): string
    {
        return $this->canonicalStrings($request, $timestamp, $nonce)[0];
    }

    /**
     * @return array<int, string>
     */
    private function canonicalStrings(Request $request, string $timestamp, string $nonce): array
    {
        $paths = array_values(array_unique([
            $this->normalizePath('/'.$request->path()),
            $this->normalizePath($request->getPathInfo()),
        ]));

        $bodyHashes = [hash('sha256', $request->getContent() ?: '')];

        if ($request->isMethod('GET') || $request->isMethod('HEAD')) {
            $bodyHashes[] = hash('sha256', '');
        }

        $canonical = [];

        foreach ($paths as $path) {
            foreach (array_values(array_unique($bodyHashes)) as $bodyHash) {
                $canonical[] = implode("\n", [
                    strtoupper($request->method()),
                    $path,
                    $timestamp,
                    $nonce,
                    $bodyHash,
                ]);
            }
        }

        return $canonical;
    }

    private function normalizePath(string $path): string
    {
        return '/'.ltrim($path, '/');
    }
}
