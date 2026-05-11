<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VapidKeySetResource\Pages\ManageVapidKeySets;
use App\Models\VapidKeySet;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class VapidKeySetResource extends Resource
{
    protected static ?string $model = VapidKeySet::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?string $navigationLabel = 'VAPID Key Sets';

    protected static string|UnitEnum|null $navigationGroup = 'Push';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('site_id')->relationship('site', 'name')->searchable()->preload(),
            Select::make('legacy_push_app_id')->relationship('legacyPushApp', 'legacy_title')->searchable()->preload(),
            TextInput::make('name')->disabled(),
            Textarea::make('public_key')->disabled()->rows(3),
            TextInput::make('source')->disabled(),
            Toggle::make('active')->disabled(),
            Textarea::make('metadata')->disabled()->json(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('site.name')->searchable()->sortable(),
                TextColumn::make('legacyPushApp.legacy_appid')->label('Legacy App')->sortable(),
                TextColumn::make('public_key')
                    ->label('Public key')
                    ->formatStateUsing(fn (?string $state): string => $state === null ? '' : mb_substr($state, 0, 12).'...'.mb_substr($state, -8)),
                TextColumn::make('source')->badge()->sortable(),
                IconColumn::make('active')->boolean(),
                TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageVapidKeySets::route('/'),
        ];
    }
}
