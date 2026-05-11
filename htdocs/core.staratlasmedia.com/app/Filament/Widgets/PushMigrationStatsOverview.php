<?php

namespace App\Filament\Widgets;

use App\Models\PushSubscription;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PushMigrationStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Legacy migration status';

    protected function getStats(): array
    {
        $legacyImported = PushSubscription::query()->where('source', 'legacy_import')->count();
        $pending = PushSubscription::query()->where('status', 'legacy_import_pending')->count();
        $reconfirmed = PushSubscription::query()->where('status', 'core_reconfirmed')->count();
        $superseded = PushSubscription::query()->where('status', 'superseded')->count();
        $coreSdk = PushSubscription::query()->where('source', 'core_sdk')->count();
        $invalid = PushSubscription::query()->where('status', 'invalid')->count();
        $rate = $legacyImported > 0 ? round((($reconfirmed + $superseded) / $legacyImported) * 100, 2) : 0;

        return [
            Stat::make('Legacy imported', number_format($legacyImported)),
            Stat::make('Legacy pending', number_format($pending)),
            Stat::make('Core reconfirmed', number_format($reconfirmed)),
            Stat::make('Superseded', number_format($superseded)),
            Stat::make('Core SDK', number_format($coreSdk)),
            Stat::make('Invalid', number_format($invalid)),
            Stat::make('Reconfirmation rate', $rate.'%'),
        ];
    }
}
