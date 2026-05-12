<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuthSessionResource\Pages\ManageAuthSessions;
use App\Models\AuthSession;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class AuthSessionResource extends Resource
{
    protected static ?string $model = AuthSession::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Auth Sessions';

    protected static string|UnitEnum|null $navigationGroup = 'Identity';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('session_uuid')->searchable()->toggleable(),
            TextColumn::make('user.email')->label('User')->searchable()->sortable(),
            TextColumn::make('site.code')->label('Site')->sortable(),
            TextColumn::make('origin')->searchable(),
            TextColumn::make('status')->badge()->sortable(),
            TextColumn::make('last_seen_at')->dateTime()->sortable(),
            TextColumn::make('expires_at')->dateTime()->sortable(),
            TextColumn::make('revoked_at')->dateTime()->sortable(),
        ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return ['index' => ManageAuthSessions::route('/')];
    }
}
