<?php

declare(strict_types=1);

namespace StarAtlas\CoreBridge\Routes;

use StarAtlas\CoreBridge\Auth\AuthController;
use StarAtlas\CoreBridge\Manifest\ManifestController;
use StarAtlas\CoreBridge\Push\PushClickController;
use StarAtlas\CoreBridge\ServiceWorker\ServiceWorkerController;
use StarAtlas\CoreBridge\Utils\Options;
use StarAtlas\CoreBridge\Utils\UrlResolver;

final class RouteManager
{
    public function __construct(
        private readonly Options $options,
        private readonly UrlResolver $resolver,
        private readonly ServiceWorkerController $serviceWorker,
        private readonly ManifestController $manifest,
        private readonly AuthController $auth,
        private readonly PushClickController $pushClick,
    ) {}

    public function register(): void
    {
        add_action('template_redirect', [$this, 'dispatch'], 0);
    }

    public function dispatch(): void
    {
        if (! $this->options->configured()) {
            return;
        }

        $requestPath = $this->resolver->requestPath();

        foreach ($this->serviceWorkerPaths() as $path) {
            if ($this->resolver->pathMatches($requestPath, $path)) {
                $this->serviceWorker->serve();
            }
        }

        foreach ($this->manifestPaths() as $path) {
            if ($this->resolver->pathMatches($requestPath, $path)) {
                $this->manifest->serve();
            }
        }

        $pwaStart = (string) $this->options->configValue('pwa_start_url', '');
        $pwaStartPath = $this->pathOnly($pwaStart);

        if ($pwaStartPath !== '' && $this->resolver->pathMatches($requestPath, $pwaStartPath)) {
            $this->manifest->pwaStart();
        }

        if ($this->resolver->pathMatches($requestPath, '/core-auth/callback')) {
            $this->auth->callback();
        }

        $token = $this->pushClickToken($requestPath);

        if ($token !== null) {
            $this->pushClick->redirect($token);
        }
    }

    /**
     * @return array<int, string>
     */
    private function serviceWorkerPaths(): array
    {
        $config = $this->options->config();
        $paths = [];

        foreach (['registration_service_worker_url', 'service_worker_url'] as $key) {
            if (! empty($config[$key]) && is_string($config[$key])) {
                $paths[] = $this->pathOnly($config[$key]);
            }
        }

        foreach (['local_service_worker_paths', 'service_worker_paths'] as $key) {
            if (! empty($config[$key]) && is_array($config[$key])) {
                foreach ($config[$key] as $path) {
                    if (is_string($path)) {
                        $paths[] = $this->pathOnly($path);
                    }
                }
            }
        }

        return array_values(array_filter(array_unique($paths)));
    }

    /**
     * @return array<int, string>
     */
    private function manifestPaths(): array
    {
        $config = $this->options->config();
        $paths = [];

        foreach (['manifest_url', 'manifest_path'] as $key) {
            if (! empty($config[$key]) && is_string($config[$key])) {
                $paths[] = $this->pathOnly($config[$key]);
            }
        }

        if (! empty($config['manifest_paths']) && is_array($config['manifest_paths'])) {
            foreach ($config['manifest_paths'] as $path) {
                if (is_string($path)) {
                    $paths[] = $this->pathOnly($path);
                }
            }
        }

        return array_values(array_filter(array_unique($paths)));
    }

    private function pathOnly(string $pathOrUrl): string
    {
        $path = wp_parse_url($pathOrUrl, PHP_URL_PATH);

        return is_string($path) ? $path : $pathOrUrl;
    }

    private function pushClickToken(string $requestPath): ?string
    {
        $prefixes = array_unique([
            $this->resolver->normalizeAbsolutePath('/core-push-click'),
            $this->resolver->pathWithBase('/core-push-click'),
        ]);

        foreach ($prefixes as $prefix) {
            $needle = rtrim($prefix, '/').'/';

            if (str_starts_with($requestPath.'/', $needle)) {
                $token = trim(substr($requestPath, strlen($needle)), '/');

                return $token === '' ? null : sanitize_text_field($token);
            }
        }

        return null;
    }
}
