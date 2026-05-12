<?php

namespace App\Services\Auth;

use App\Models\AuthProvider;
use App\Models\AuthProviderSiteSetting;
use App\Models\BridgeInstallation;
use App\Models\PushGroup;
use App\Models\Site;
use Illuminate\Database\Eloquent\Collection;

class AuthProviderResolver
{
    /**
     * @return Collection<int, AuthProvider>
     */
    public function providers(?Site $site = null, ?PushGroup $pushGroup = null, ?BridgeInstallation $bridgeInstallation = null): Collection
    {
        $providers = AuthProvider::query()
            ->with('siteSettings')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $providers->map(function (AuthProvider $provider) use ($site, $pushGroup, $bridgeInstallation): AuthProvider {
            $override = $this->bestOverride($provider, $site, $pushGroup, $bridgeInstallation);

            if ($override !== null && $override->status !== 'inherited') {
                $provider->setAttribute('effective_status', $override->status);
                $provider->setAttribute('effective_config_json', array_replace(
                    $provider->config_json ?? [],
                    $override->config_json ?? [],
                ));

                return $provider;
            }

            $provider->setAttribute('effective_status', $provider->status);
            $provider->setAttribute('effective_config_json', $provider->config_json ?? []);

            return $provider;
        });
    }

    /**
     * @return Collection<int, AuthProvider>
     */
    public function publicEnabledProviders(?Site $site = null, ?PushGroup $pushGroup = null, ?BridgeInstallation $bridgeInstallation = null): Collection
    {
        return $this->providers($site, $pushGroup, $bridgeInstallation)
            ->filter(fn (AuthProvider $provider): bool => $provider->getAttribute('effective_status') === 'enabled' && $provider->is_public)
            ->values();
    }

    public function publicEnabledProvider(string $code, ?Site $site = null, ?PushGroup $pushGroup = null, ?BridgeInstallation $bridgeInstallation = null): ?AuthProvider
    {
        return $this->publicEnabledProviders($site, $pushGroup, $bridgeInstallation)
            ->firstWhere('code', $code);
    }

    private function bestOverride(AuthProvider $provider, ?Site $site, ?PushGroup $pushGroup, ?BridgeInstallation $bridgeInstallation): ?AuthProviderSiteSetting
    {
        $settings = $provider->siteSettings;

        if ($bridgeInstallation !== null) {
            $setting = $settings->firstWhere('bridge_installation_id', $bridgeInstallation->id);

            if ($setting !== null) {
                return $setting;
            }
        }

        if ($pushGroup !== null) {
            $setting = $settings->firstWhere('push_group_id', $pushGroup->id);

            if ($setting !== null) {
                return $setting;
            }
        }

        if ($site !== null) {
            $setting = $settings->firstWhere('site_id', $site->id);

            if ($setting !== null) {
                return $setting;
            }
        }

        return null;
    }
}
