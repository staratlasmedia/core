<?php

declare(strict_types=1);

namespace StarAtlas\CoreBridge\Update;

use StarAtlas\CoreBridge\Api\CoreClient;
use StarAtlas\CoreBridge\Utils\Options;
use StarAtlas\CoreBridge\Utils\UrlResolver;
use stdClass;

final class PluginUpdateChecker
{
    private UpdateClient $updates;

    public function __construct(
        private readonly Options $options,
        CoreClient $client,
    ) {
        $resolver = new UrlResolver($options);
        $this->updates = new UpdateClient($options, $client, $resolver);
    }

    public function register(): void
    {
        add_filter('pre_set_site_transient_update_plugins', [$this, 'filterUpdates']);
        add_filter('plugins_api', [$this, 'pluginInfo'], 10, 3);
        add_action('upgrader_process_complete', [$this, 'afterUpgrade'], 10, 2);
    }

    public function filterUpdates(mixed $transient): mixed
    {
        if (! is_object($transient)) {
            return $transient;
        }

        $metadata = $this->updates->check();
        $entry = $this->entry($metadata);

        if (! isset($transient->response) || ! is_array($transient->response)) {
            $transient->response = [];
        }

        if (! isset($transient->no_update) || ! is_array($transient->no_update)) {
            $transient->no_update = [];
        }

        if (($metadata['ok'] ?? false) === true && (bool) ($metadata['update_available'] ?? false)) {
            $transient->response[STAR_ATLAS_CORE_BRIDGE_BASENAME] = $entry;
            unset($transient->no_update[STAR_ATLAS_CORE_BRIDGE_BASENAME]);
        } else {
            $transient->no_update[STAR_ATLAS_CORE_BRIDGE_BASENAME] = $entry;
            unset($transient->response[STAR_ATLAS_CORE_BRIDGE_BASENAME]);
        }

        return $transient;
    }

    public function pluginInfo(mixed $result, string $action, mixed $args): mixed
    {
        if ($action !== 'plugin_information' || ! is_object($args) || ($args->slug ?? '') !== 'star-atlas-core-bridge') {
            return $result;
        }

        $info = $this->updates->info();
        $object = new stdClass();
        $object->name = (string) ($info['name'] ?? 'Star Atlas Core Bridge');
        $object->slug = 'star-atlas-core-bridge';
        $object->version = (string) ($info['version'] ?? STAR_ATLAS_CORE_BRIDGE_VERSION);
        $object->requires = (string) ($info['requires_wp'] ?? '6.5');
        $object->tested = (string) ($info['tested_wp'] ?? '');
        $object->requires_php = (string) ($info['requires_php'] ?? '8.2');
        $object->sections = [
            'description' => __('Local WordPress bridge for Star Atlas Core.', 'star-atlas-core-bridge'),
            'changelog' => wp_kses_post((string) ($info['changelog'] ?? $info['release_notes'] ?? '')),
        ];

        return $object;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function afterUpgrade(mixed $upgrader, array $options): void
    {
        if (($options['action'] ?? '') !== 'update' || ($options['type'] ?? '') !== 'plugin') {
            return;
        }

        $plugins = is_array($options['plugins'] ?? null) ? $options['plugins'] : [];

        if (in_array(STAR_ATLAS_CORE_BRIDGE_BASENAME, $plugins, true)) {
            $this->options->update(['update_status' => 'updated']);
        }
    }

    /**
     * @param array<string, mixed> $metadata
     */
    private function entry(array $metadata): stdClass
    {
        $entry = new stdClass();
        $entry->id = 'star-atlas-core-bridge';
        $entry->slug = 'star-atlas-core-bridge';
        $entry->plugin = STAR_ATLAS_CORE_BRIDGE_BASENAME;
        $entry->new_version = (string) ($metadata['version'] ?? STAR_ATLAS_CORE_BRIDGE_VERSION);
        $entry->url = (string) ($metadata['homepage'] ?? STAR_ATLAS_CORE_BRIDGE_CORE_URL);
        $entry->package = (string) ($metadata['download_url'] ?? '');
        $entry->requires = (string) ($metadata['requires_wp'] ?? '6.5');
        $entry->tested = (string) ($metadata['tested_wp'] ?? '');
        $entry->requires_php = (string) ($metadata['requires_php'] ?? '8.2');

        return $entry;
    }
}
