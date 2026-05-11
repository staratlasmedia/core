<?php

namespace App\Filament\Widgets;

use App\Models\PushReconfirmationEvent;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class DailyReconfirmationsChart extends ChartWidget
{
    protected ?string $heading = 'Daily reconfirmations';

    protected function getData(): array
    {
        $start = Carbon::today()->subDays(13);
        $days = collect(range(0, 13))->map(fn (int $offset): string => $start->copy()->addDays($offset)->toDateString());
        $counts = PushReconfirmationEvent::query()
            ->whereDate('created_at', '>=', $start)
            ->whereIn('new_status', ['core_reconfirmed', 'superseded'])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        return [
            'datasets' => [
                [
                    'label' => 'Reconfirmed',
                    'data' => $days->map(fn (string $day): int => (int) ($counts[$day] ?? 0))->all(),
                ],
            ],
            'labels' => $days->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
