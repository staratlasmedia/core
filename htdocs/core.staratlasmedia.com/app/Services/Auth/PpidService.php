<?php

namespace App\Services\Auth;

use App\Models\PublisherProvidedId;
use App\Models\Site;
use App\Models\User;

class PpidService
{
    public function siteScoped(User $user, Site $site): PublisherProvidedId
    {
        return $this->firstOrCreate($user, $site, 'site', $site->id);
    }

    public function networkScoped(User $user): PublisherProvidedId
    {
        return $this->firstOrCreate($user, null, 'network', 'network');
    }

    private function firstOrCreate(User $user, ?Site $site, string $scope, string|int $scopeKey): PublisherProvidedId
    {
        $version = (int) config('core.auth.ppid_version', 1);

        return PublisherProvidedId::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'site_id' => $site?->id,
                'scope' => $scope,
            ],
            [
                'ppid' => $this->generate($user, $scope, $scopeKey, $version),
                'version' => $version,
            ],
        );
    }

    private function generate(User $user, string $scope, string|int $scopeKey, int $version): string
    {
        $secret = (string) config('core.auth.ppid_secret', '');
        $material = implode(':', [$version, $scope, $scopeKey, $user->uuid]);

        return 'ppid_'.$version.'_'.hash_hmac('sha256', $material, $secret);
    }
}
