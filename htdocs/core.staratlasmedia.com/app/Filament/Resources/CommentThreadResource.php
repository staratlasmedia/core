<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommentThreadResource\Pages\ManageCommentThreads;
use App\Models\CommentThread;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class CommentThreadResource extends Resource
{
    protected static ?string $model = CommentThread::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $navigationLabel = 'Threads';

    protected static string|UnitEnum|null $navigationGroup = 'Comments';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('site_id')->relationship('site', 'name')->searchable()->preload()->required(),
            Select::make('push_group_id')->relationship('pushGroup', 'name')->searchable()->preload(),
            Select::make('bridge_installation_id')->relationship('bridgeInstallation', 'uuid')->searchable()->preload(),
            TextInput::make('source_url')->required()->url(),
            TextInput::make('source_title'),
            TextInput::make('language')->maxLength(16),
            TextInput::make('section'),
            Select::make('status')
                ->options([
                    CommentThread::STATUS_OPEN => 'Open',
                    CommentThread::STATUS_CLOSED => 'Closed',
                    CommentThread::STATUS_ARCHIVED => 'Archived',
                    CommentThread::STATUS_DISABLED => 'Disabled',
                ])
                ->required(),
            Textarea::make('metadata_json')->json()->rows(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('site.code')->label('Site')->searchable()->sortable(),
                TextColumn::make('pushGroup.code')->label('Push Group')->sortable(),
                TextColumn::make('source_title')->searchable()->limit(40),
                TextColumn::make('source_url')->searchable()->limit(56),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('approved_comments_count')->label('Approved')->sortable(),
                TextColumn::make('pending_comments_count')->label('Pending')->sortable(),
                TextColumn::make('reported_comments_count')->label('Reported')->sortable(),
                TextColumn::make('last_commented_at')->dateTime()->sortable(),
            ])
            ->recordActions([
                Action::make('openThread')->label('Open')->action(fn (CommentThread $record): bool => $record->forceFill(['status' => CommentThread::STATUS_OPEN])->save()),
                Action::make('closeThread')->label('Close')->action(fn (CommentThread $record): bool => $record->forceFill(['status' => CommentThread::STATUS_CLOSED, 'closed_at' => now()])->save()),
                Action::make('disableThread')->label('Disable')->requiresConfirmation()->action(fn (CommentThread $record): bool => $record->forceFill(['status' => CommentThread::STATUS_DISABLED])->save()),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCommentThreads::route('/'),
        ];
    }
}
