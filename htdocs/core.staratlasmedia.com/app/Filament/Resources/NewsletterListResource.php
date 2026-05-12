<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsletterListResource\Pages\ManageNewsletterLists;
use App\Models\NewsletterList;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class NewsletterListResource extends Resource
{
    protected static ?string $model = NewsletterList::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;
    protected static string|UnitEnum|null $navigationGroup = 'Newsletter';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('site_id')->relationship('site', 'name')->searchable()->preload()->required(),
            Select::make('push_group_id')->relationship('pushGroup', 'name')->searchable()->preload(),
            TextInput::make('code')->required(),
            TextInput::make('slug')->required(),
            TextInput::make('name')->required(),
            Textarea::make('description')->rows(3),
            TextInput::make('language')->maxLength(16),
            Select::make('status')->options(['active' => 'Active', 'disabled' => 'Disabled', 'archived' => 'Archived'])->default('active'),
            Select::make('default_from_identity_id')->relationship('senderIdentity', 'code')->searchable()->preload(),
            Checkbox::make('double_opt_in'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->searchable()->sortable(),
            TextColumn::make('name')->searchable(),
            TextColumn::make('site.code')->label('Site'),
            TextColumn::make('language')->sortable(),
            TextColumn::make('status')->badge(),
            TextColumn::make('subscribers_count')->counts('subscribers')->label('Subscribers'),
        ])->headerActions([CreateAction::make()])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageNewsletterLists::route('/')];
    }
}
