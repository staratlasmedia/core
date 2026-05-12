<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebAuthnCredentialResource\Pages\ManageWebAuthnCredentials;
use App\Models\WebAuthnCredential;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class WebAuthnCredentialResource extends Resource
{
    protected static ?string $model = WebAuthnCredential::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'WebAuthn Credentials';

    protected static string|UnitEnum|null $navigationGroup = 'Identity';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('user.email')->label('User')->searchable()->sortable(),
            TextColumn::make('credential_id_hash')->label('Credential Hash')->toggleable(),
            TextColumn::make('name')->searchable(),
            TextColumn::make('aaguid')->toggleable(),
            TextColumn::make('sign_count')->sortable(),
            TextColumn::make('last_used_at')->dateTime()->sortable(),
        ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return ['index' => ManageWebAuthnCredentials::route('/')];
    }
}
