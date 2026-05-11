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

require_once __DIR__.'/includes/class-star-atlas-core-bridge.php';

add_action('plugins_loaded', static function (): void {
    Star_Atlas_Core_Bridge::boot();
});
