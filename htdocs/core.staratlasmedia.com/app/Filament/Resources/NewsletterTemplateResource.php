<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsletterTemplateResource\Pages\ManageNewsletterTemplates;
use App\Models\NewsletterTemplate;
use BackedEnum;
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

class NewsletterTemplateResource extends Resource
{
    protected static ?string $model = NewsletterTemplate::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;
    protected static string|UnitEnum|null $navigationGroup = 'Newsletter';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('site_id')->relationship('site', 'name')->searchable()->preload(),
            TextInput::make('name')->required(),
            Select::make('type')->options(['campaign' => 'Campaign', 'digest' => 'Digest', 'confirmation' => 'Confirmation', 'unsubscribe' => 'Unsubscribe', 'preferences' => 'Preferences'])->default('campaign'),
            TextInput::make('subject_template'),
            TextInput::make('preheader_template'),
            Textarea::make('html_template')->rows(8),
            Textarea::make('text_template')->rows(5),
            Textarea::make('editor_schema_json')->json()->rows(5),
            Select::make('status')->options(['active' => 'Active', 'archived' => 'Archived'])->default('active'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable(),
            TextColumn::make('type')->badge(),
            TextColumn::make('status')->badge(),
        ])->headerActions([CreateAction::make()])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageNewsletterTemplates::route('/')];
    }
}
