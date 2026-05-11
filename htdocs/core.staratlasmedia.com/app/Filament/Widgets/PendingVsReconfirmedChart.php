<?php

namespace App\Filament\Widgets;

use App\Models\PushSubscription;
use Filament\Widgets\ChartWidget;

class PendingVsReconfirmedChart extends ChartWidget
{
    protected ?string $heading = 'Pending vs reconfirmed';

    protected function getData(): array
    {
        $pending = PushSubscription::query()->where('status', 'legacy_import_pending')->count();
        $reconfirmed = PushSubscription::query()->where('status', 'core_reconfirmed')->count();

        return [
            'datasets' => [
                [
                    'label' => 'Subscriptions',
                    'data' => [$pending, $reconfirmed],
                ],
            ],
            'labels' => ['Pending', 'Reconfirmed'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
