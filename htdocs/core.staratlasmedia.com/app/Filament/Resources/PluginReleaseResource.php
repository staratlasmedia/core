<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PluginReleaseResource\Pages\ManagePluginReleases;
use App\Models\PluginRelease;
use BackedEnum;
use Filament\Actions\Action;
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

class PluginReleaseResource extends Resource
{
    protected static ?string $model = PluginRelease::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowDownTray;

    protected static ?string $navigationLabel = 'Plugin Releases';

    protected static string|UnitEnum|null $navigationGroup = 'WordPress Bridge';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('plugin_package_id')->relationship('pluginPackage', 'name')->required()->searchable()->preload(),
            TextInput::make('version')->required(),
            Select::make('channel')->options(['stable' => 'Stable', 'beta' => 'Beta', 'internal' => 'Internal'])->default('stable')->required(),
            Select::make('status')->options(['draft' => 'Draft', 'published' => 'Published', 'revoked' => 'Revoked'])->default('draft')->required(),
            TextInput::make('zip_storage_path'),
            TextInput::make('zip_sha256')->maxLength(64),
            TextInput::make('zip_size_bytes')->numeric(),
            TextInput::make('requires_wp'),
            TextInput::make('tested_wp'),
            TextInput::make('requires_php'),
            Textarea::make('changelog')->rows(5),
            Textarea::make('release_notes')->rows(5),
            Textarea::make('metadata_json')->json()->rows(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('pluginPackage.code')->label('Package')->searchable()->sortable(),
                TextColumn::make('version')->searchable()->sortable(),
                TextColumn::make('channel')->badge()->sortable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('zip_sha256')->toggleable(),
                TextColumn::make('published_at')->dateTime()->sortable(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                Action::make('publish')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->visible(fn (PluginRelease $record): bool => $record->status !== 'published')
                    ->action(fn (PluginRelease $record): bool => $record->forceFill([
                        'status' => 'published',
                        'published_at' => now(),
                        'revoked_at' => null,
                    ])->save()),
                Action::make('revoke')
                    ->icon(Heroicon::OutlinedNoSymbol)
                    ->requiresConfirmation()
                    ->visible(fn (PluginRelease $record): bool => $record->status !== 'revoked')
                    ->action(fn (PluginRelease $record): bool => $record->forceFill([
                        'status' => 'revoked',
                        'revoked_at' => now(),
                    ])->save()),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePluginReleases::route('/'),
        ];
    }
}
