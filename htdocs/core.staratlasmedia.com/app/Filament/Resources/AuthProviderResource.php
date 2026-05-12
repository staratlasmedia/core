<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuthProviderResource\Pages\ManageAuthProviders;
use App\Models\AuthProvider;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
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

class AuthProviderResource extends Resource
{
    protected static ?string $model = AuthProvider::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Auth Providers';

    protected static string|UnitEnum|null $navigationGroup = 'Identity';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')->required()->maxLength(255),
            TextInput::make('name')->required()->maxLength(255),
            Select::make('type')->options([
                'passkey' => 'Passkey',
                'oauth' => 'OAuth',
                'magic_link' => 'Magic Link',
                'password' => 'Password',
            ])->required(),
            Select::make('status')->options([
                'disabled' => 'Disabled',
                'enabled' => 'Enabled',
                'hidden' => 'Hidden',
            ])->default('disabled')->required(),
            TextInput::make('sort_order')->numeric()->default(100)->required(),
            Checkbox::make('is_default'),
            Checkbox::make('is_public'),
            Textarea::make('config_json')->json()->rows(6),
            Textarea::make('encrypted_config_json')
                ->password()
                ->revealable(false)
                ->dehydrated(fn (?string $state): bool => filled($state))
                ->helperText('Write-only encrypted provider secrets. Existing values are never shown.'),
            Textarea::make('metadata_json')->json()->rows(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->searchable()->sortable(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('type')->badge()->sortable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('sort_order')->sortable(),
                IconColumn::make('is_default')->boolean(),
                IconColumn::make('is_public')->boolean(),
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
            'index' => ManageAuthProviders::route('/'),
        ];
    }
}
