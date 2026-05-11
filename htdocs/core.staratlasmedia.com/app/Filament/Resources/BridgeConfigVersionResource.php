<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BridgeConfigVersionResource\Pages\ManageBridgeConfigVersions;
use App\Models\BridgeConfigVersion;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class BridgeConfigVersionResource extends Resource
{
    protected static ?string $model = BridgeConfigVersion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentDuplicate;

    protected static ?string $navigationLabel = 'Bridge Config Versions';

    protected static string|UnitEnum|null $navigationGroup = 'WordPress Bridge';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('bridge_installation_id')->relationship('bridgeInstallation', 'uuid')->searchable()->preload(),
            Select::make('site_id')->relationship('site', 'name')->required()->searchable()->preload(),
            Select::make('push_group_id')->relationship('pushGroup', 'name')->searchable()->preload(),
            TextInput::make('version')->numeric()->required(),
            Textarea::make('config_json')->json()->rows(10)->required(),
            TextInput::make('checksum')->required()->maxLength(64),
            Select::make('active')->options([1 => 'Active', 0 => 'Inactive'])->default(0)->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('bridgeInstallation.uuid')->label('Installation')->searchable()->toggleable(),
                TextColumn::make('site.code')->label('Site')->searchable()->sortable(),
                TextColumn::make('pushGroup.code')->label('Push Group')->sortable(),
                TextColumn::make('version')->sortable(),
                TextColumn::make('checksum')->searchable()->toggleable(),
                IconColumn::make('active')->boolean()->sortable(),
                TextColumn::make('published_at')->dateTime()->sortable(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageBridgeConfigVersions::route('/'),
        ];
    }
}
