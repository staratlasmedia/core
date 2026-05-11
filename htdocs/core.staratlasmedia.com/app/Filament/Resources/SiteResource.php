<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiteResource\Pages\ManageSites;
use App\Models\Site;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
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

class SiteResource extends Resource
{
    protected static ?string $model = Site::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static ?string $navigationLabel = 'Sites';

    protected static string|UnitEnum|null $navigationGroup = 'Core';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')->required()->maxLength(255),
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('canonical_origin')->required()->url()->maxLength(255),
            TextInput::make('language')->maxLength(16),
            Select::make('push_group_id')->relationship('pushGroup', 'name')->searchable()->preload(),
            TextInput::make('push_group')->maxLength(255),
            Select::make('status')
                ->options([
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                ])
                ->default('active')
                ->required(),
            Textarea::make('metadata')->json(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->searchable()->sortable(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('canonical_origin')->searchable(),
                TextColumn::make('language')->sortable(),
                TextColumn::make('pushGroup.code')->label('Push Group')->sortable(),
                TextColumn::make('push_group')->sortable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('updated_at')->dateTime()->sortable(),
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
            'index' => ManageSites::route('/'),
        ];
    }
}
