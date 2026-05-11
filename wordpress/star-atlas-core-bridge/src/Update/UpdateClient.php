<?php

declare(strict_types=1);

namespace StarAtlas\CoreBridge\Update;

use StarAtlas\CoreBridge\Api\CoreClient;
use StarAtlas\CoreBridge\Utils\Options;
use StarAtlas\CoreBridge\Utils\UrlResolver;

final class UpdateClient
{
    public function __construct(
        private readonly Options $options,
        private readonly CoreClient $client,
        private readonly UrlResolver $resolver,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function check(): array
    {
        if ($this->options->updateChecksDisabled()) {
            return ['ok' => true, 'update_available' => false, 'message' => 'Update checks are disabled.'];
        }

        $response = $this->client->updateCheck($this->options->releaseChannel());
        $downloadUrl = isset($response['download_url']) && is_string($response['download_url'])
            ? $this->resolver->absoluteCoreUrl($response['download_url'])
            : '';

        $this->options->update([
            'last_update_check' => gmdate('c'),
            'latest_version' => (string) ($response['version'] ?? ''),
            'update_status' => ($response['ok'] ?? false) === true
                ? ((bool) ($response['update_available'] ?? false) ? 'update_available' : 'current')
                : 'error',
            'last_update_response' => array_merge($response, ['download_url' => $downloadUrl]),
        ]);

        return array_merge($response, ['download_url' => $downloadUrl]);
    }

    /**
     * @return array<string, mixed>
     */
    public function info(): array
    {
        return $this->client->pluginInfo($this->options->releaseChannel());
    }
}
