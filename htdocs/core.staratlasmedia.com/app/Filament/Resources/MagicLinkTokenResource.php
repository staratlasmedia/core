<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MagicLinkTokenResource\Pages\ManageMagicLinkTokens;
use App\Models\MagicLinkToken;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class MagicLinkTokenResource extends Resource
{
    protected static ?string $model = MagicLinkToken::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Magic Link Tokens';

    protected static string|UnitEnum|null $navigationGroup = 'Identity';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('email')->searchable()->sortable(),
            TextColumn::make('user.email')->label('User')->searchable()->sortable(),
            TextColumn::make('site.code')->label('Site')->sortable(),
            TextColumn::make('bridgeInstallation.uuid')->label('Bridge')->toggleable(),
            TextColumn::make('status')->badge()->sortable(),
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
        return ['index' => ManageMagicLinkTokens::route('/')];
    }
}
