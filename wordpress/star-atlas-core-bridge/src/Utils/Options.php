<?php

declare(strict_types=1);

namespace StarAtlas\CoreBridge\Utils;

final class Options
{
    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        $stored = get_option(STAR_ATLAS_CORE_BRIDGE_OPTION_NAME, []);

        return array_replace_recursive($this->defaults(), is_array($stored) ? $stored : []);
    }

    /**
     * @return array<string, mixed>
     */
    public function defaults(): array
    {
        return [
            'bridge_installation_id' => '',
            'bridge_secret' => '',
            'bridge_secret_fingerprint' => '',
            'config' => [],
            'release_channel' => 'stable',
            'last_connection_check' => '',
            'connection_status' => '',
            'last_config_sync' => '',
            'last_update_check' => '',
            'latest_version' => '',
            'update_status' => '',
            'update_checks_disabled' => false,
            'last_update_response' => [],
        ];
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $options = $this->all();

        return $options[$key] ?? $default;
    }

    public function update(array $values): void
    {
        update_option(STAR_ATLAS_CORE_BRIDGE_OPTION_NAME, array_replace_recursive($this->all(), $values), false);
    }

    public function reset(): void
    {
        delete_option(STAR_ATLAS_CORE_BRIDGE_OPTION_NAME);
    }

    public function configured(): bool
    {
        return $this->installationId() !== '' && $this->secret() !== '' && is_array($this->config()) && $this->config() !== [];
    }

    public function installationId(): string
    {
        return (string) $this->get('bridge_installation_id', '');
    }

    public function secret(): string
    {
        return (string) $this->get('bridge_secret', '');
    }

    /**
     * @return array<string, mixed>
     */
    public function config(): array
    {
        $config = $this->get('config', []);

        return is_array($config) ? $config : [];
    }

    public function configValue(string $key, mixed $default = null): mixed
    {
        $config = $this->config();

        return $config[$key] ?? $default;
    }

    public function releaseChannel(): string
    {
        $channel = (string) $this->get('release_channel', 'stable');

        return in_array($channel, ['stable', 'beta', 'internal'], true) ? $channel : 'stable';
    }

    public function updateChecksDisabled(): bool
    {
        return (bool) $this->get('update_checks_disabled', false);
    }
}
