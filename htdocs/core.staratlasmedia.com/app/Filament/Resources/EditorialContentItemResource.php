<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EditorialContentItemResource\Pages\ManageEditorialContentItems;
use App\Models\EditorialContentItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class EditorialContentItemResource extends Resource
{
    protected static ?string $model = EditorialContentItem::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;
    protected static string|UnitEnum|null $navigationGroup = 'Editorial Content';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->searchable()->limit(60),
            TextColumn::make('source.code')->label('Source')->sortable(),
            TextColumn::make('source_type')->badge(),
            TextColumn::make('post_type')->badge(),
            TextColumn::make('language')->sortable(),
            TextColumn::make('section')->sortable(),
            TextColumn::make('published_at')->dateTime()->sortable(),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageEditorialContentItems::route('/')];
    }
}
