<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SocialIdentityResource\Pages\ManageSocialIdentities;
use App\Models\SocialIdentity;
use BackedEnum;
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

class SocialIdentityResource extends Resource
{
    protected static ?string $model = SocialIdentity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Social Identities';

    protected static string|UnitEnum|null $navigationGroup = 'Identity';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')->relationship('user', 'email')->required()->searchable()->preload(),
            TextInput::make('provider')->required(),
            TextInput::make('provider_id')->required(),
            TextInput::make('provider_user_id'),
            TextInput::make('email')->email(),
            TextInput::make('name'),
            TextInput::make('avatar_url')->url(),
            TextInput::make('access_token_encrypted')->password()->revealable(false)->dehydrated(fn (?string $state): bool => filled($state))->helperText('Write-only.'),
            TextInput::make('refresh_token_encrypted')->password()->revealable(false)->dehydrated(fn (?string $state): bool => filled($state))->helperText('Write-only.'),
            Textarea::make('metadata')->json()->rows(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.email')->label('User')->searchable()->sortable(),
                TextColumn::make('provider')->badge()->sortable(),
                TextColumn::make('provider_user_id')->searchable()->toggleable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageSocialIdentities::route('/')];
    }
}
