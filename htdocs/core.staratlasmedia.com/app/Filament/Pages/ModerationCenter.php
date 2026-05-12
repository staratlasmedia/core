<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CommentAnalyticsStatsOverview;
use App\Filament\Widgets\CommentModerationQueueTable;
use App\Filament\Widgets\TopCommentThreadsTable;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ModerationCenter extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Comments';

    protected static ?string $navigationLabel = 'Moderation Center';

    protected static ?string $title = 'Moderation Center';

    protected string $view = 'filament.pages.moderation-center';

    protected function getHeaderWidgets(): array
    {
        return [
            CommentAnalyticsStatsOverview::class,
            CommentModerationQueueTable::class,
            TopCommentThreadsTable::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 2;
    }
}
