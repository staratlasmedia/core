<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DailyReconfirmationsChart;
use App\Filament\Widgets\LegacyPushAppSummaryTable;
use App\Filament\Widgets\PendingVsReconfirmedChart;
use App\Filament\Widgets\PushMigrationStatsOverview;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class PushMigrationDashboard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static string|UnitEnum|null $navigationGroup = 'Push';

    protected static ?string $navigationLabel = 'Push Migration';

    protected static ?string $title = 'Push Migration';

    protected string $view = 'filament.pages.push-migration-dashboard';

    protected function getHeaderWidgets(): array
    {
        return [
            PushMigrationStatsOverview::class,
            DailyReconfirmationsChart::class,
            PendingVsReconfirmedChart::class,
            LegacyPushAppSummaryTable::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 2;
    }
}
