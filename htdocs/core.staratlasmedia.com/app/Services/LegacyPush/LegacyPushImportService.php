<?php

namespace App\Services\LegacyPush;

use App\Models\LegacyPushApp;
use App\Models\PushSubscription;
use App\Models\Site;
use App\Models\SiteOrigin;
use App\Models\VapidKeySet;
use Illuminate\Support\Carbon;

class LegacyPushImportService
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $resolvedApps = [];

    /**
     * @var array<int, array<string, mixed>|null>
     */
    private array $resolvedVapid = [];

    /**
     * @var array<int, array{site: Site, origin: SiteOrigin, legacy_app: LegacyPushApp, vapid: VapidKeySet}>
     */
    private array $persistedApps = [];

    public function __construct(
        private readonly LegacyPushReader $reader,
        private readonly LegacyPushSettingsParser $settingsParser,
        private readonly LegacyPushTokenParser $tokenParser,
        private readonly LegacyPushAppMapper $appMapper,
        private readonly LegacyPushVapidResolver $vapidResolver,
    ) {}

    /**
     * @param  array<int>  $appids
     * @return array<string, mixed>
     */
    public function inspect(array $appids = []): array
    {
        return $this->baseReport($appids);
    }

    /**
     * @param  array<int>  $appids
     * @return array<string, mixed>
     */
    public function run(array $appids = [], bool $dryRun = true, int $chunk = 1000, ?int $limit = null): array
    {
        $report = $this->baseReport($appids);
        $seenHashes = [];
        $processed = 0;
        $stop = false;
        $chunk = max(1, $chunk);

        $this->reader->eligibleDeviceQuery($appids)->chunkById($chunk, function ($rows) use (&$report, &$seenHashes, &$processed, &$stop, $limit, $dryRun): bool {
            $readyRows = [];
            $endpointHashes = [];

            foreach ($rows as $row) {
                if ($limit !== null && $processed >= $limit) {
                    $stop = true;

                    break;
                }

                $processed++;
                $report['eligible_rows']++;

                $result = $this->prepareRow($row);

                if ($result['status'] !== 'ready') {
                    $report[$result['status']]++;

                    continue;
                }

                $endpointHash = $result['token']['endpoint_hash'];

                if (isset($seenHashes[$endpointHash])) {
                    $report['duplicate_endpoint_hashes']++;

                    continue;
                }

                $seenHashes[$endpointHash] = true;

                if (count($report['sample_endpoint_hashes']) < 5) {
                    $report['sample_endpoint_hashes'][] = $endpointHash;
                }

                $readyRows[] = $result;
                $endpointHashes[] = $endpointHash;
            }

            $existingByHash = PushSubscription::query()
                ->whereIn('endpoint_hash', $endpointHashes)
                ->get()
                ->keyBy('endpoint_hash');

            foreach ($readyRows as $result) {
                $endpointHash = $result['token']['endpoint_hash'];
                $existing = $existingByHash->get($endpointHash);

                if ($existing !== null && $existing->source !== 'legacy_import') {
                    $report['existing_core_matches']++;

                    continue;
                }

                $report['migrable_rows']++;

                if ($existing === null) {
                    $report['planned_inserts']++;
                } else {
                    $report['planned_updates']++;
                    $report['existing_legacy_matches']++;
                }

                if (! $dryRun) {
                    $this->persistPreparedRow($result, $existing);

                    if ($existing === null) {
                        $report['inserted']++;
                    } else {
                        $report['updated']++;
                    }
                }
            }

            return ! $stop;
        });

        return $report;
    }

    /**
     * @return array<string, mixed>
     */
    private function prepareRow(object $row): array
    {
        $mapping = $this->mappingFor((int) $row->appid);

        if ($mapping === null) {
            return ['status' => 'unmapped_rows'];
        }

        $vapid = $this->vapidFor((int) $row->appid);

        if ($vapid === null) {
            return ['status' => 'missing_vapid_rows'];
        }

        $token = $this->tokenParser->parse(is_string($row->token) ? $row->token : null);

        if ($token === null) {
            return ['status' => 'malformed_rows'];
        }

        return [
            'status' => 'ready',
            'row' => $row,
            'mapping' => $mapping,
            'vapid' => $vapid,
            'token' => $token,
        ];
    }

    /**
     * @param  array<string, mixed>  $prepared
     */
    private function persistPreparedRow(array $prepared, ?PushSubscription $existing): void
    {
        $row = $prepared['row'];
        $mapping = $prepared['mapping'];
        $token = $prepared['token'];
        $platforms = $this->appMapper->allowedPlatforms();
        $persisted = $this->persistedAppFor((int) $row->appid, $mapping, $prepared['vapid']);

        $attributes = [
            'site_id' => $persisted['site']->id,
            'site_origin_id' => $persisted['origin']->id,
            'source' => 'legacy_import',
            'status' => 'legacy_import_pending',
            'legacy_push_app_id' => $persisted['legacy_app']->id,
            'legacy_appid' => (int) $row->appid,
            'legacy_device_id' => (string) $row->id,
            'legacy_userid' => (string) $row->userid,
            'platform_id' => (int) $row->platid,
            'platform_name' => $platforms[(int) $row->platid] ?? null,
            'origin' => $mapping['origin'],
            'service_worker_url' => $mapping['service_worker_url'],
            'service_worker_scope' => $mapping['service_worker_scope'],
            'endpoint_encrypted' => $token['endpoint'],
            'p256dh_encrypted' => $token['p256dh'],
            'auth_encrypted' => $token['auth'],
            'vapid_key_set_id' => $persisted['vapid']->id,
            'language' => $mapping['language'] ?? null,
            'section' => $mapping['section'] ?? null,
            'merge_group' => $mapping['merge_group'] ?? null,
            'created_at_legacy' => $this->timestamp($row->created_date ?? null),
            'last_active_at_legacy' => $this->timestamp($row->last_active_time ?? null),
        ];

        if ($existing === null) {
            PushSubscription::query()->create($attributes + [
                'endpoint_hash' => $token['endpoint_hash'],
            ]);

            return;
        }

        $existing->fill($attributes);
        $existing->save();
    }

    /**
     * @param  array<string, mixed>  $mapping
     * @param  array<string, mixed>  $vapid
     * @return array{site: Site, origin: SiteOrigin, legacy_app: LegacyPushApp, vapid: VapidKeySet}
     */
    private function persistedAppFor(int $appid, array $mapping, array $vapid): array
    {
        if (isset($this->persistedApps[$appid])) {
            return $this->persistedApps[$appid];
        }

        $site = Site::query()->updateOrCreate(
            ['code' => $mapping['site_code']],
            [
                'name' => $mapping['name'],
                'canonical_origin' => $mapping['origin'],
                'language' => $mapping['language'] ?? null,
                'push_group' => $mapping['push_group'] ?? null,
                'status' => 'active',
            ],
        );

        $origin = SiteOrigin::query()->updateOrCreate(
            [
                'origin' => $mapping['origin'],
                'path_prefix' => $mapping['path_prefix'],
            ],
            [
                'site_id' => $site->id,
                'is_primary' => $mapping['path_prefix'] === '/',
                'status' => 'active',
            ],
        );

        $legacyApp = LegacyPushApp::query()->updateOrCreate(
            ['legacy_appid' => $appid],
            [
                'site_id' => $site->id,
                'origin' => $mapping['origin'],
                'language' => $mapping['language'] ?? null,
                'section' => $mapping['section'] ?? null,
                'merge_group' => $mapping['merge_group'] ?? null,
                'service_worker_url' => $mapping['service_worker_url'],
                'service_worker_scope' => $mapping['service_worker_scope'],
                'legacy_title' => $mapping['name'],
                'metadata' => [
                    'full_service_worker_url' => $mapping['full_service_worker_url'],
                    'path_prefix' => $mapping['path_prefix'],
                ],
            ],
        );

        $vapidKeySet = VapidKeySet::query()->updateOrCreate(
            [
                'legacy_push_app_id' => $legacyApp->id,
                'source' => 'legacy_import',
            ],
            [
                'site_id' => $site->id,
                'name' => 'Legacy VAPID appid '.$appid,
                'public_key' => $vapid['public_key'],
                'private_key_encrypted' => $vapid['private_key'],
                'active' => true,
                'metadata' => [
                    'legacy_appid' => $appid,
                    'vapid_source' => $vapid['source'],
                    'public_key_hash' => $vapid['public_key_hash'],
                ],
            ],
        );

        $legacyApp->forceFill(['vapid_key_set_id' => $vapidKeySet->id])->save();

        return $this->persistedApps[$appid] = [
            'site' => $site,
            'origin' => $origin,
            'legacy_app' => $legacyApp,
            'vapid' => $vapidKeySet,
        ];
    }

    /**
     * @param  array<int>  $appids
     * @return array<string, mixed>
     */
    private function baseReport(array $appids): array
    {
        $totals = [];
        $platformCounts = [];

        foreach ($this->reader->totalsByAppid($appids) as $row) {
            $totals[(int) $row->appid] = (int) $row->total;
        }

        foreach ($this->reader->platformCounts($appids) as $row) {
            $appid = (int) $row->appid;
            $platid = (int) $row->platid;
            $platformCounts[$appid][$platid] = (int) $row->total;
        }

        $selectedAppids = $appids === [] ? array_keys($this->appMapper->configuredMappings()) : $appids;
        $vapidSources = [];

        foreach ($selectedAppids as $appid) {
            $appid = (int) $appid;
            $vapid = $this->vapidFor($appid);
            $mapping = $this->mappingFor($appid);

            $vapidSources[$appid] = [
                'site_code' => $mapping['site_code'] ?? null,
                'source' => $vapid['source'] ?? 'missing',
                'public_key_hash' => $vapid['public_key_hash'] ?? null,
                'private_key_present' => $vapid !== null,
            ];
        }

        return [
            'totals_by_appid' => $totals,
            'platform_counts' => $platformCounts,
            'vapid_sources' => $vapidSources,
            'eligible_rows' => 0,
            'migrable_rows' => 0,
            'malformed_rows' => 0,
            'missing_vapid_rows' => 0,
            'unmapped_rows' => 0,
            'duplicate_endpoint_hashes' => 0,
            'existing_core_matches' => 0,
            'existing_legacy_matches' => 0,
            'planned_inserts' => 0,
            'planned_updates' => 0,
            'inserted' => 0,
            'updated' => 0,
            'sample_endpoint_hashes' => [],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function mappingFor(int $appid): ?array
    {
        if (array_key_exists($appid, $this->resolvedApps)) {
            return $this->resolvedApps[$appid];
        }

        $settings = $this->settingsParser->parse($this->reader->chromePlatformSettings($appid));

        return $this->resolvedApps[$appid] = $this->appMapper->resolve($appid, $settings);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function vapidFor(int $appid): ?array
    {
        if (array_key_exists($appid, $this->resolvedVapid)) {
            return $this->resolvedVapid[$appid];
        }

        return $this->resolvedVapid[$appid] = $this->vapidResolver->resolve($appid);
    }

    private function timestamp(mixed $value): ?Carbon
    {
        if (! is_numeric($value) || (int) $value <= 0) {
            return null;
        }

        return Carbon::createFromTimestamp((int) $value);
    }
}
