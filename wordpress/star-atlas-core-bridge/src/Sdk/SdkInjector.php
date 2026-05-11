<?php

declare(strict_types=1);

namespace StarAtlas\CoreBridge\Sdk;

use StarAtlas\CoreBridge\Utils\Options;
use StarAtlas\CoreBridge\Utils\PageContext;

final class SdkInjector
{
    public function __construct(
        private readonly Options $options,
        private readonly PageContext $context,
    ) {}

    public function register(): void
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueue']);
    }

    public function enqueue(): void
    {
        if (is_admin() || ! $this->options->configured()) {
            return;
        }

        $config = array_merge($this->options->config(), $this->context->current());
        $sdkUrl = (string) ($config['sdk_url'] ?? '');

        if ($sdkUrl === '') {
            return;
        }

        wp_register_script(
            'star-atlas-core-sdk',
            esc_url_raw($sdkUrl),
            [],
            STAR_ATLAS_CORE_BRIDGE_VERSION,
            true
        );

        wp_add_inline_script(
            'star-atlas-core-sdk',
            'window.StarAtlasCoreConfig = '.wp_json_encode($config, JSON_UNESCAPED_SLASHES).'; window.StarAtlasCore = window.StarAtlasCoreConfig;',
            'before'
        );

        wp_enqueue_script('star-atlas-core-sdk');
    }
}
