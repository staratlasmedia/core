<?php

namespace App\Services\LegacyPush;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LegacyPushReader
{
    public function connection(): ConnectionInterface
    {
        return DB::connection(config('core.legacy_push.connection', 'legacy_push'));
    }

    /**
     * @param  array<int>  $appids
     * @return Collection<int, object>
     */
    public function totalsByAppid(array $appids = []): Collection
    {
        return $this->deviceQuery($appids)
            ->selectRaw('appid, COUNT(*) as total')
            ->groupBy('appid')
            ->orderBy('appid')
            ->get();
    }

    /**
     * @param  array<int>  $appids
     * @return Collection<int, object>
     */
    public function platformCounts(array $appids = []): Collection
    {
        return $this->deviceQuery($appids)
            ->selectRaw('appid, platid, COUNT(*) as total')
            ->groupBy('appid', 'platid')
            ->orderBy('appid')
            ->orderBy('platid')
            ->get();
    }

    /**
     * @return array<string, string>
     */
    public function globalVapidSettings(): array
    {
        return $this->connection()
            ->table('setting')
            ->whereIn('varname', ['vapid_public', 'vapid_private'])
            ->pluck('value', 'varname')
            ->all();
    }

    public function chromePlatformSettings(int $appid): ?string
    {
        $settings = $this->connection()
            ->table('apps_platfom')
            ->where('appid', $appid)
            ->where('platid', 5)
            ->value('settings');

        return is_string($settings) ? $settings : null;
    }

    /**
     * @param  array<int>  $appids
     */
    public function eligibleDeviceQuery(array $appids = []): Builder
    {
        $allowedPlatforms = array_keys(config('core.legacy_push.allowed_platforms', []));

        return $this->deviceQuery($appids)
            ->select([
                'id',
                'userid',
                'appid',
                'platid',
                'token',
                'created_date',
                'last_active_time',
                'status',
                'firebase',
            ])
            ->whereIn('platid', $allowedPlatforms)
            ->where('status', 1)
            ->where('firebase', 0)
            ->orderBy('id');
    }

    /**
     * @param  array<int>  $appids
     */
    private function deviceQuery(array $appids = []): Builder
    {
        $query = $this->connection()->table('devices');

        if ($appids !== []) {
            $query->whereIn('appid', $appids);
        }

        return $query;
    }
}
