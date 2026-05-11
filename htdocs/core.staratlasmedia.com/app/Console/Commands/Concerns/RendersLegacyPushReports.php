<?php

namespace App\Console\Commands\Concerns;

trait RendersLegacyPushReports
{
    /**
     * @return array<int>
     */
    protected function appidsOption(): array
    {
        $value = $this->option('appids');

        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(
            static fn (string $part): int => (int) trim($part),
            explode(',', $value),
        ))));
    }

    protected function renderReport(array $report, bool $includeImportCounts = false): void
    {
        $this->newLine();
        $this->info('Totals by appid');
        $this->table(
            ['appid', 'rows'],
            collect($report['totals_by_appid'])->map(
                static fn (int $total, int $appid): array => [$appid, $total],
            )->values()->all(),
        );

        $this->info('Platform counts');
        $platformRows = [];

        foreach ($report['platform_counts'] as $appid => $counts) {
            foreach ($counts as $platid => $total) {
                $platformRows[] = [$appid, $platid, $total];
            }
        }

        $this->table(['appid', 'platid', 'rows'], $platformRows);

        $this->info('VAPID sources');
        $this->table(
            ['appid', 'site_code', 'source', 'public_key_hash', 'private_key_present'],
            collect($report['vapid_sources'])->map(static fn (array $source, int $appid): array => [
                $appid,
                $source['site_code'] ?? '',
                $source['source'],
                $source['public_key_hash'] ?? '',
                $source['private_key_present'] ? 'yes' : 'no',
            ])->values()->all(),
        );

        $this->info('Import plan');
        $rows = [
            ['eligible_rows', $report['eligible_rows']],
            ['migrable_rows', $report['migrable_rows']],
            ['malformed_rows', $report['malformed_rows']],
            ['missing_vapid_rows', $report['missing_vapid_rows']],
            ['unmapped_rows', $report['unmapped_rows']],
            ['duplicate_endpoint_hashes', $report['duplicate_endpoint_hashes']],
            ['existing_core_matches', $report['existing_core_matches']],
            ['existing_legacy_matches', $report['existing_legacy_matches']],
            ['planned_inserts', $report['planned_inserts']],
            ['planned_updates', $report['planned_updates']],
        ];

        if ($includeImportCounts) {
            $rows[] = ['inserted', $report['inserted']];
            $rows[] = ['updated', $report['updated']];
        }

        $this->table(['metric', 'value'], $rows);

        $this->info('Sample endpoint hashes');
        $this->table(
            ['endpoint_hash'],
            collect($report['sample_endpoint_hashes'])->map(static fn (string $hash): array => [$hash])->all(),
        );
    }
}
