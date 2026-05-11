<?php

namespace App\Filament\Widgets;

use App\Models\LegacyPushApp;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LegacyPushAppSummaryTable extends TableWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->heading('Legacy app mapping')
            ->query(
                LegacyPushApp::query()
                    ->with('site', 'pushGroup')
                    ->withCount('pushSubscriptions')
                    ->orderBy('legacy_appid'),
            )
            ->columns([
                TextColumn::make('legacy_appid')->label('App ID')->sortable(),
                TextColumn::make('site.code')->label('Site')->searchable(),
                TextColumn::make('pushGroup.code')->label('Push Group')->searchable(),
                TextColumn::make('section')->sortable(),
                TextColumn::make('push_subscriptions_count')->label('Subscriptions')->sortable(),
            ]);
    }
}
