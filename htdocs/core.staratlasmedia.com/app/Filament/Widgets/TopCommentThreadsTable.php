<?php

namespace App\Filament\Widgets;

use App\Models\CommentThread;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class TopCommentThreadsTable extends TableWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->heading('Top comment threads')
            ->query(
                CommentThread::query()
                    ->with('site', 'pushGroup')
                    ->orderByDesc('approved_comments_count')
                    ->orderByDesc('reported_comments_count'),
            )
            ->columns([
                TextColumn::make('site.code')->label('Site')->sortable(),
                TextColumn::make('pushGroup.code')->label('Push Group')->sortable(),
                TextColumn::make('source_title')->label('Title')->limit(40)->searchable(),
                TextColumn::make('source_url')->label('URL')->limit(56)->searchable(),
                TextColumn::make('approved_comments_count')->label('Approved')->sortable(),
                TextColumn::make('reported_comments_count')->label('Reported')->sortable(),
                TextColumn::make('status')->badge()->sortable(),
            ]);
    }
}
