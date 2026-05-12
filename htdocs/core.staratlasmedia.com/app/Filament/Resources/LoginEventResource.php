<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoginEventResource\Pages\ManageLoginEvents;
use App\Models\LoginEvent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class LoginEventResource extends Resource
{
    protected static ?string $model = LoginEvent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Login Events';

    protected static string|UnitEnum|null $navigationGroup = 'Identity';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('created_at')->dateTime()->sortable(),
            TextColumn::make('user.email')->label('User')->searchable()->sortable(),
            TextColumn::make('site.code')->label('Site')->sortable(),
            TextColumn::make('bridgeInstallation.uuid')->label('Bridge')->toggleable(),
            TextColumn::make('event_type')->badge()->sortable(),
            TextColumn::make('provider')->badge()->sortable(),
            TextColumn::make('result')->badge()->sortable(),
            TextColumn::make('origin')->searchable(),
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
        return ['index' => ManageLoginEvents::route('/')];
    }
}
