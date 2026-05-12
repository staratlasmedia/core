<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebAuthnChallengeResource\Pages\ManageWebAuthnChallenges;
use App\Models\WebAuthnChallenge;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class WebAuthnChallengeResource extends Resource
{
    protected static ?string $model = WebAuthnChallenge::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'WebAuthn Challenges';

    protected static string|UnitEnum|null $navigationGroup = 'Identity';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('user.email')->label('User')->searchable()->sortable(),
            TextColumn::make('type')->badge()->sortable(),
            TextColumn::make('rp_id')->sortable(),
            TextColumn::make('origin')->searchable(),
            TextColumn::make('expires_at')->dateTime()->sortable(),
            TextColumn::make('consumed_at')->dateTime()->sortable(),
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
        return ['index' => ManageWebAuthnChallenges::route('/')];
    }
}
