<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommentModerationEventResource\Pages\ManageCommentModerationEvents;
use App\Models\CommentModerationEvent;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class CommentModerationEventResource extends Resource
{
    protected static ?string $model = CommentModerationEvent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $navigationLabel = 'Moderation Events';

    protected static string|UnitEnum|null $navigationGroup = 'Comments';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('event_type')->disabled(),
            TextInput::make('old_status')->disabled(),
            TextInput::make('new_status')->disabled(),
            Textarea::make('reason')->disabled()->rows(3),
            Textarea::make('metadata_json')->json()->disabled()->rows(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('site.code')->label('Site')->sortable(),
                TextColumn::make('comment.uuid')->label('Comment')->searchable(),
                TextColumn::make('event_type')->badge()->sortable(),
                TextColumn::make('old_status')->badge()->sortable(),
                TextColumn::make('new_status')->badge()->sortable(),
                TextColumn::make('moderator.name')->label('Moderator')->searchable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCommentModerationEvents::route('/'),
        ];
    }
}
