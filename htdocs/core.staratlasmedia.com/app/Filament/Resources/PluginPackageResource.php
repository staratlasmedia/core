<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PluginPackageResource\Pages\ManagePluginPackages;
use App\Models\PluginPackage;
use BackedEnum;
use Filament\Actions\CreateAction;
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

class PluginPackageResource extends Resource
{
    protected static ?string $model = PluginPackage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static ?string $navigationLabel = 'Plugin Packages';

    protected static string|UnitEnum|null $navigationGroup = 'WordPress Bridge';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')->required()->unique(ignoreRecord: true),
            TextInput::make('name')->required(),
            TextInput::make('slug')->required()->unique(ignoreRecord: true),
            TextInput::make('current_stable_version'),
            TextInput::make('current_beta_version'),
            Select::make('status')->options(['active' => 'Active', 'archived' => 'Archived'])->default('active')->required(),
            Textarea::make('metadata_json')->json()->rows(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->searchable()->sortable(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('slug')->searchable(),
                TextColumn::make('current_stable_version')->label('Stable'),
                TextColumn::make('current_beta_version')->label('Beta'),
                TextColumn::make('status')->badge()->sortable(),
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
            'index' => ManagePluginPackages::route('/'),
        ];
    }
}
