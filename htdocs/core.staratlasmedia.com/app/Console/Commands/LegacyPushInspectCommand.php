<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\RendersLegacyPushReports;
use App\Services\LegacyPush\LegacyPushImportService;
use Illuminate\Console\Command;

class LegacyPushInspectCommand extends Command
{
    use RendersLegacyPushReports;

    protected $signature = 'core:legacy-push:inspect {--appids= : Comma-separated legacy app IDs to inspect}';

    protected $description = 'Inspect legacy Web Push data without printing endpoints or private keys.';

    public function handle(LegacyPushImportService $service): int
    {
        $report = $service->inspect($this->appidsOption());

        $this->renderReport($report);

        return self::SUCCESS;
    }
}
