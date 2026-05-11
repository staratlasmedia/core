<?php

declare(strict_types=1);

namespace StarAtlas\CoreBridge;

use StarAtlas\CoreBridge\Admin\AdminPage;
use StarAtlas\CoreBridge\Api\CoreClient;
use StarAtlas\CoreBridge\Auth\AuthController;
use StarAtlas\CoreBridge\Manifest\ManifestController;
use StarAtlas\CoreBridge\Push\PushClickController;
use StarAtlas\CoreBridge\Routes\RouteManager;
use StarAtlas\CoreBridge\Sdk\SdkInjector;
use StarAtlas\CoreBridge\ServiceWorker\ServiceWorkerController;
use StarAtlas\CoreBridge\Setup\SetupService;
use StarAtlas\CoreBridge\Update\PluginUpdateChecker;
use StarAtlas\CoreBridge\Utils\Options;
use StarAtlas\CoreBridge\Utils\PageContext;
use StarAtlas\CoreBridge\Utils\UrlResolver;

final class Plugin
{
    public static function boot(): void
    {
        $options = new Options();
        $resolver = new UrlResolver($options);
        $client = new CoreClient($options, $resolver);

        (new AdminPage($options, $resolver, new SetupService($options, $resolver, $client), $client))->register();
        (new SdkInjector($options, new PageContext($options, $resolver)))->register();
        (new RouteManager(
            $options,
            $resolver,
            new ServiceWorkerController($options),
            new ManifestController($options, $resolver),
            new AuthController($options, $client),
            new PushClickController($options, $client)
        ))->register();
        (new PluginUpdateChecker($options, $client))->register();
    }
}
