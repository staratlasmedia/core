<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BridgeInstallationResource\Pages\ManageBridgeInstallations;
use App\Models\BridgeInstallation;
use App\Services\Bridge\BridgeConfigBuilder;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use UnitEnum;

class BridgeInstallationResource extends Resource
{
    protected static ?string $model = BridgeInstallation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPuzzlePiece;

    protected static ?string $navigationLabel = 'Bridge Installations';

    protected static string|UnitEnum|null $navigationGroup = 'WordPress Bridge';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('site_id')->relationship('site', 'name')->searchable()->preload()->required(),
            Select::make('push_group_id')->relationship('pushGroup', 'name')->searchable()->preload(),
            Select::make('site_origin_id')->relationship('siteOrigin', 'origin')->searchable()->preload(),
            TextInput::make('site_code')->required(),
            TextInput::make('push_group_code'),
            TextInput::make('language')->maxLength(16),
            TextInput::make('section'),
            TextInput::make('origin')->required()->url(),
            TextInput::make('wp_home_url')->required()->url(),
            TextInput::make('wp_site_url')->url(),
            TextInput::make('detected_base_path')->required(),
            TextInput::make('plugin_version'),
            TextInput::make('wordpress_version'),
            TextInput::make('php_version'),
            Select::make('status')
                ->options([
                    'active' => 'Active',
                    'disabled' => 'Disabled',
                    'error' => 'Error',
                    'revoked' => 'Revoked',
                ])
                ->required(),
            Textarea::make('metadata_json')->json()->rows(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('uuid')->searchable()->toggleable(),
                TextColumn::make('site.code')->label('Site')->searchable()->sortable(),
                TextColumn::make('pushGroup.code')->label('Push Group')->sortable(),
                TextColumn::make('origin')->searchable(),
                TextColumn::make('detected_base_path')->label('Base Path'),
                TextColumn::make('plugin_version')->sortable(),
                TextColumn::make('wordpress_version')->label('WP')->sortable(),
                TextColumn::make('php_version')->label('PHP')->sortable(),
                TextColumn::make('bridge_secret_fingerprint')->label('Secret Fingerprint')->toggleable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('last_seen_at')->dateTime()->sortable(),
            ])
            ->recordActions([
                Action::make('previewConfig')
                    ->label('Preview config')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->modalSubmitAction(false)
                    ->modalContent(fn (BridgeInstallation $record): HtmlString => self::codePreview(app(BridgeConfigBuilder::class)->forInstallation($record))),
                Action::make('revoke')
                    ->requiresConfirmation()
                    ->icon(Heroicon::OutlinedNoSymbol)
                    ->visible(fn (BridgeInstallation $record): bool => $record->status !== 'revoked')
                    ->action(fn (BridgeInstallation $record): bool => $record->forceFill(['status' => 'revoked'])->save()),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageBridgeInstallations::route('/'),
        ];
    }

    /**
     * @param  array<string, mixed>  $code
     */
    private static function codePreview(array $code): HtmlString
    {
        return new HtmlString('<pre class="overflow-auto rounded-lg bg-gray-950 p-4 text-xs leading-5 text-gray-100">'.e(json_encode($code, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)).'</pre>');
    }
}
