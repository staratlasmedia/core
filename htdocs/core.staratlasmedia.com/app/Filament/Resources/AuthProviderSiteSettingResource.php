<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuthProviderSiteSettingResource\Pages\ManageAuthProviderSiteSettings;
use App\Models\AuthProviderSiteSetting;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class AuthProviderSiteSettingResource extends Resource
{
    protected static ?string $model = AuthProviderSiteSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Provider Overrides';

    protected static string|UnitEnum|null $navigationGroup = 'Identity';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('auth_provider_id')->relationship('authProvider', 'name')->required()->searchable()->preload(),
            Select::make('site_id')->relationship('site', 'name')->searchable()->preload(),
            Select::make('push_group_id')->relationship('pushGroup', 'name')->searchable()->preload(),
            Select::make('bridge_installation_id')->relationship('bridgeInstallation', 'uuid')->searchable()->preload(),
            Select::make('status')->options([
                'inherited' => 'Inherited',
                'enabled' => 'Enabled',
                'disabled' => 'Disabled',
                'hidden' => 'Hidden',
            ])->default('inherited')->required(),
            Textarea::make('config_json')->json()->rows(5),
            Textarea::make('encrypted_config_json')
                ->password()
                ->revealable(false)
                ->dehydrated(fn (?string $state): bool => filled($state))
                ->helperText('Write-only encrypted override secrets. Existing values are never shown.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('authProvider.code')->label('Provider')->searchable()->sortable(),
                TextColumn::make('site.code')->label('Site')->sortable(),
                TextColumn::make('pushGroup.code')->label('Push Group')->sortable(),
                TextColumn::make('bridgeInstallation.uuid')->label('Bridge')->toggleable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('updated_at')->dateTime()->sortable(),
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
            'index' => ManageAuthProviderSiteSettings::route('/'),
        ];
    }
}
