<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommentReactionResource\Pages\ManageCommentReactions;
use App\Models\CommentReaction;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class CommentReactionResource extends Resource
{
    protected static ?string $model = CommentReaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHandThumbUp;

    protected static ?string $navigationLabel = 'Reactions';

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
            TextInput::make('reaction_type')->disabled(),
            TextInput::make('anonymous_id')->disabled(),
            TextInput::make('ip_hash')->disabled(),
            TextInput::make('user_agent_hash')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('site.code')->label('Site')->sortable(),
                TextColumn::make('comment.uuid')->label('Comment')->searchable(),
                TextColumn::make('reaction_type')->badge()->sortable(),
                TextColumn::make('user.name')->label('User')->searchable(),
                TextColumn::make('anonymous_id')->limit(16)->toggleable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCommentReactions::route('/'),
        ];
    }
}
