<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PluginUpdateDownloadResource\Pages\ManagePluginUpdateDownloads;
use App\Models\PluginUpdateDownload;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class PluginUpdateDownloadResource extends Resource
{
    protected static ?string $model = PluginUpdateDownload::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentArrowDown;

    protected static ?string $navigationLabel = 'Plugin Downloads';

    protected static string|UnitEnum|null $navigationGroup = 'WordPress Bridge';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('pluginRelease.version')->label('Release')->sortable(),
            TextColumn::make('pluginRelease.channel')->label('Channel')->badge(),
            TextColumn::make('bridgeInstallation.uuid')->label('Installation')->searchable()->toggleable(),
            TextColumn::make('site.code')->label('Site')->sortable(),
            TextColumn::make('status')->badge()->sortable(),
            TextColumn::make('expires_at')->dateTime()->sortable(),
            TextColumn::make('downloaded_at')->dateTime()->sortable(),
        ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePluginUpdateDownloads::route('/'),
        ];
    }
}
