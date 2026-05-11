<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LegacyPushAppResource\Pages\ManageLegacyPushApps;
use App\Models\LegacyPushApp;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class LegacyPushAppResource extends Resource
{
    protected static ?string $model = LegacyPushApp::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBellAlert;

    protected static ?string $navigationLabel = 'Legacy Push Apps';

    protected static string|UnitEnum|null $navigationGroup = 'Push';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('legacy_appid')->numeric()->required(),
            Select::make('site_id')->relationship('site', 'name')->searchable()->preload()->required(),
            TextInput::make('origin')->required()->url()->maxLength(255),
            TextInput::make('language')->maxLength(16),
            TextInput::make('section')->maxLength(255),
            TextInput::make('merge_group')->maxLength(255),
            TextInput::make('service_worker_url')->required()->maxLength(255),
            TextInput::make('service_worker_scope')->required()->maxLength(255),
            Select::make('vapid_key_set_id')->relationship('vapidKeySet', 'name')->searchable()->preload(),
            TextInput::make('legacy_title')->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('legacy_appid')->sortable(),
                TextColumn::make('site.name')->searchable()->sortable(),
                TextColumn::make('origin')->searchable(),
                TextColumn::make('language')->sortable(),
                TextColumn::make('section')->sortable(),
                TextColumn::make('merge_group')->sortable(),
                TextColumn::make('service_worker_url')->searchable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageLegacyPushApps::route('/'),
        ];
    }
}
