<?php

namespace App\Filament\Widgets;

use App\Models\Comment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class CommentModerationQueueTable extends TableWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->heading('Moderation queue')
            ->query(
                Comment::query()
                    ->with('site', 'thread')
                    ->where(function ($query): void {
                        $query
                            ->whereIn('status', [Comment::STATUS_PENDING, Comment::STATUS_REJECTED, Comment::STATUS_SPAM])
                            ->orWhere('reports_count', '>', 0);
                    })
                    ->latest(),
            )
            ->columns([
                TextColumn::make('site.code')->label('Site')->sortable(),
                TextColumn::make('thread.source_title')->label('Thread')->limit(32),
                TextColumn::make('author_display_name')->label('Author')->searchable(),
                TextColumn::make('body')->limit(64)->searchable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('reports_count')->label('Reports')->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ]);
    }
}
