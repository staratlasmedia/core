<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\RendersLegacyPushReports;
use App\Services\LegacyPush\LegacyPushImportService;
use Illuminate\Console\Command;

class LegacyPushImportCommand extends Command
{
    use RendersLegacyPushReports;

    protected $signature = 'core:legacy-push:import
        {--dry-run : Inspect planned inserts without writing Core records}
        {--appids= : Comma-separated legacy app IDs to import}
        {--chunk=1000 : Reporting chunk size}
        {--limit= : Maximum eligible legacy rows to scan}';

    protected $description = 'Import legacy Web Push subscriptions into Core as encrypted pending records.';

    public function handle(LegacyPushImportService $service): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $chunk = max(1, (int) $this->option('chunk'));
        $limitOption = $this->option('limit');
        $limit = is_numeric($limitOption) ? max(0, (int) $limitOption) : null;

        $report = $service->run(
            appids: $this->appidsOption(),
            dryRun: $dryRun,
            chunk: $chunk,
            limit: $limit,
        );

        $this->renderReport($report, includeImportCounts: ! $dryRun);

        if ($dryRun) {
            $this->warn('Dry run only: no Core records were written.');
        }

        return self::SUCCESS;
    }
}
