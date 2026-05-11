<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiteOriginResource\Pages\ManageSiteOrigins;
use App\Models\SiteOrigin;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class SiteOriginResource extends Resource
{
    protected static ?string $model = SiteOrigin::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static ?string $navigationLabel = 'Site Origins';

    protected static string|UnitEnum|null $navigationGroup = 'Core';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('site_id')->relationship('site', 'name')->searchable()->preload()->required(),
            TextInput::make('origin')->required()->url()->maxLength(255),
            TextInput::make('path_prefix')->maxLength(255),
            Toggle::make('is_primary'),
            Select::make('status')
                ->options([
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                ])
                ->default('active')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('site.name')->searchable()->sortable(),
                TextColumn::make('origin')->searchable()->sortable(),
                TextColumn::make('path_prefix')->searchable(),
                IconColumn::make('is_primary')->boolean(),
                TextColumn::make('status')->badge()->sortable(),
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
            'index' => ManageSiteOrigins::route('/'),
        ];
    }
}
