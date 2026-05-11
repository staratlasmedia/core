<?php
/**
 * Plugin Name: Star Atlas Core Bridge
 * Description: Local WordPress bridge for Star Atlas Core SDK, Service Workers, manifests, and SSO callbacks.
 * Version: 0.1.0
 * Author: Star Atlas Media
 * Requires at least: 6.5
 * Requires PHP: 8.2
 * Text Domain: star-atlas-core-bridge
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

define('STAR_ATLAS_CORE_BRIDGE_VERSION', '0.1.0');
define('STAR_ATLAS_CORE_BRIDGE_FILE', __FILE__);
define('STAR_ATLAS_CORE_BRIDGE_DIR', __DIR__);
define('STAR_ATLAS_CORE_BRIDGE_BASENAME', plugin_basename(__FILE__));
define('STAR_ATLAS_CORE_BRIDGE_OPTION_NAME', 'star_atlas_core_bridge');
define('STAR_ATLAS_CORE_BRIDGE_CORE_URL', 'https://core.staratlasmedia.com');

spl_autoload_register(static function (string $class): void {
    $prefix = 'StarAtlas\\CoreBridge\\';

    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = STAR_ATLAS_CORE_BRIDGE_DIR.'/src/'.str_replace('\\', '/', $relative).'.php';

    if (is_readable($file)) {
        require_once $file;
    }
});

add_action('plugins_loaded', [StarAtlas\CoreBridge\Plugin::class, 'boot']);
