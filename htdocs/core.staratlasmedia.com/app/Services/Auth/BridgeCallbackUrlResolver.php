<?php

namespace App\Services\Auth;

use App\Models\BridgeInstallation;

class BridgeCallbackUrlResolver
{
    public function callbackUrl(BridgeInstallation $installation): string
    {
        $basePath = $this->basePath($installation);

        return rtrim($installation->origin, '/').$this->pathWithBase($basePath, '/core-auth/callback');
    }

    public function basePath(BridgeInstallation $installation): string
    {
        $futureBasePath = $installation->getAttribute('wp_base_path');
        $detectedBasePath = $installation->getAttribute('detected_base_path');

        // Priority is explicit WordPress base path first, then detected Phase 6 path, then root.
        return $this->normalizeBasePath(
            is_string($futureBasePath) && $futureBasePath !== ''
                ? $futureBasePath
                : (is_string($detectedBasePath) && $detectedBasePath !== '' ? $detectedBasePath : '/')
        );
    }

    private function pathWithBase(string $basePath, string $path): string
    {
        $path = '/'.ltrim($path, '/');

        if ($basePath === '/') {
            return $path;
        }

        return rtrim($basePath, '/').$path;
    }

    private function normalizeBasePath(string $path): string
    {
        $path = '/'.trim($path, '/');

        return $path === '/' ? '/' : $path.'/';
    }
}
