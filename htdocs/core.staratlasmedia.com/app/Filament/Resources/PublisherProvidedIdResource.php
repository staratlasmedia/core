<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PublisherProvidedIdResource\Pages\ManagePublisherProvidedIds;
use App\Models\PublisherProvidedId;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class PublisherProvidedIdResource extends Resource
{
    protected static ?string $model = PublisherProvidedId::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Publisher Provided IDs';

    protected static string|UnitEnum|null $navigationGroup = 'Identity';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')->relationship('user', 'email')->disabled()->dehydrated(false),
            Select::make('site_id')->relationship('site', 'code')->disabled()->dehydrated(false),
            Select::make('scope')->options(['site' => 'Site', 'network' => 'Network'])->disabled()->dehydrated(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('user.email')->label('User')->searchable()->sortable(),
            TextColumn::make('site.code')->label('Site')->sortable(),
            TextColumn::make('scope')->badge()->sortable(),
            TextColumn::make('ppid')->searchable()->toggleable(),
            TextColumn::make('version')->sortable(),
            TextColumn::make('updated_at')->dateTime()->sortable(),
        ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return ['index' => ManagePublisherProvidedIds::route('/')];
    }
}
