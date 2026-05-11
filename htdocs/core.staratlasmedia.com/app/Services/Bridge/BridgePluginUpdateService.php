<?php

namespace App\Services\Bridge;

use App\Models\BridgeInstallation;
use App\Models\PluginPackage;
use App\Models\PluginRelease;

class BridgePluginUpdateService
{
    public function __construct(private readonly PluginDownloadTokenFactory $downloadTokenFactory) {}

    public function latestPublishedRelease(string $channel = 'stable'): ?PluginRelease
    {
        return PluginRelease::query()
            ->whereHas('pluginPackage', fn ($query) => $query
                ->where('code', config('core.bridge.plugin_package_code', 'star-atlas-core-bridge'))
                ->where('status', 'active'))
            ->where('channel', $channel)
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    public function updateMetadata(BridgeInstallation $installation, string $channel = 'stable'): array
    {
        $release = $this->latestPublishedRelease($channel);
        $package = PluginPackage::query()->where('code', config('core.bridge.plugin_package_code', 'star-atlas-core-bridge'))->first();

        if (! $release instanceof PluginRelease || ! $package instanceof PluginPackage) {
            return [
                'update_available' => false,
                'package' => $package?->slug ?? 'star-atlas-core-bridge',
                'channel' => $channel,
            ];
        }

        $current = $installation->plugin_version ?: '0.0.0';
        $available = version_compare($release->version, $current, '>');
        $download = $available ? $this->downloadTokenFactory->issue($release, $installation) : null;

        return [
            'update_available' => $available,
            'package' => $package->slug,
            'name' => $package->name,
            'channel' => $release->channel,
            'version' => $release->version,
            'current_version' => $current,
            'requires_wp' => $release->requires_wp,
            'tested_wp' => $release->tested_wp,
            'requires_php' => $release->requires_php,
            'zip_sha256' => $release->zip_sha256,
            'download_url' => $download === null ? null : route('bridge.plugin.download', ['token' => $download['token']], absolute: false),
            'changelog' => $release->changelog,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function pluginInfo(string $channel = 'stable'): array
    {
        $release = $this->latestPublishedRelease($channel);
        $package = PluginPackage::query()->where('code', config('core.bridge.plugin_package_code', 'star-atlas-core-bridge'))->first();

        return [
            'name' => $package?->name ?? 'Star Atlas Core Bridge',
            'slug' => $package?->slug ?? 'star-atlas-core-bridge',
            'channel' => $channel,
            'version' => $release?->version,
            'requires_wp' => $release?->requires_wp,
            'tested_wp' => $release?->tested_wp,
            'requires_php' => $release?->requires_php,
            'changelog' => $release?->changelog,
            'release_notes' => $release?->release_notes,
        ];
    }
}
