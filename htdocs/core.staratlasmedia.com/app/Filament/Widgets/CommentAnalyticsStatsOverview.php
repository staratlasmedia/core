<?php

namespace App\Filament\Widgets;

use App\Models\Comment;
use App\Models\CommentReport;
use App\Models\CommentThread;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CommentAnalyticsStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Comment analytics';

    protected function getStats(): array
    {
        $total = Comment::query()->count();
        $approved = Comment::query()->where('status', Comment::STATUS_APPROVED)->count();
        $pending = Comment::query()->where('status', Comment::STATUS_PENDING)->count();
        $rejected = Comment::query()->whereIn('status', [Comment::STATUS_REJECTED, Comment::STATUS_SPAM])->count();
        $reported = CommentReport::query()->where('status', CommentReport::STATUS_OPEN)->count();
        $threads = CommentThread::query()->count();
        $approvalRate = $total > 0 ? round(($approved / $total) * 100, 2) : 0;

        return [
            Stat::make('Total comments', number_format($total)),
            Stat::make('Approved', number_format($approved)),
            Stat::make('Pending', number_format($pending)),
            Stat::make('Rejected / spam', number_format($rejected)),
            Stat::make('Open reports', number_format($reported)),
            Stat::make('Threads', number_format($threads)),
            Stat::make('Approval rate', $approvalRate.'%'),
        ];
    }
}
