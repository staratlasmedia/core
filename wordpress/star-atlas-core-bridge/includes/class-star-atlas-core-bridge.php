<?php

declare(strict_types=1);

final class Star_Atlas_Core_Bridge
{
    public static function boot(): void
    {
        add_action('wp_enqueue_scripts', [self::class, 'enqueueSdk']);
    }

    public static function enqueueSdk(): void
    {
        $core_url = apply_filters('star_atlas_core_bridge_core_url', 'https://core.staratlasmedia.com');

        wp_register_script(
            'star-atlas-core-sdk',
            esc_url_raw($core_url.'/sdk/core-sdk.es.js'),
            [],
            STAR_ATLAS_CORE_BRIDGE_VERSION,
            true
        );

        wp_add_inline_script(
            'star-atlas-core-sdk',
            'window.StarAtlasCore = window.StarAtlasCore || {};',
            'before'
        );

        wp_enqueue_script('star-atlas-core-sdk');
    }
}
