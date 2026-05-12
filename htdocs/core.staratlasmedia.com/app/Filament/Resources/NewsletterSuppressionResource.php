<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsletterSuppressionResource\Pages\ManageNewsletterSuppressions;
use App\Models\NewsletterSuppression;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class NewsletterSuppressionResource extends Resource
{
    protected static ?string $model = NewsletterSuppression::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNoSymbol;
    protected static string|UnitEnum|null $navigationGroup = 'Newsletter';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('email_hash')->required()->helperText('Store hashes only, never plaintext emails.'),
            Select::make('reason')->options(['unsubscribe' => 'Unsubscribe', 'hard_bounce' => 'Hard bounce', 'complaint' => 'Complaint', 'manual' => 'Manual', 'invalid' => 'Invalid'])->required(),
            Select::make('scope')->options(['global' => 'Global', 'site' => 'Site', 'list' => 'List'])->default('global'),
            Select::make('site_id')->relationship('site', 'name')->searchable()->preload(),
            Select::make('newsletter_list_id')->relationship('list', 'name')->searchable()->preload(),
            TextInput::make('source'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('email_hash')->limit(16)->searchable(),
            TextColumn::make('reason')->badge(),
            TextColumn::make('scope')->badge(),
            TextColumn::make('suppressed_at')->dateTime()->sortable(),
        ])->headerActions([CreateAction::make()])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageNewsletterSuppressions::route('/')];
    }
}
