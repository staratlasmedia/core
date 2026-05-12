<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AiPromptTemplateResource\Pages\ManageAiPromptTemplates;
use App\Models\AiPromptTemplate;
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

class AiPromptTemplateResource extends Resource
{
    protected static ?string $model = AiPromptTemplate::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;
    protected static string|UnitEnum|null $navigationGroup = 'AI';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')->required()->unique(ignoreRecord: true),
            TextInput::make('name')->required(),
            TextInput::make('purpose')->default('generic'),
            Select::make('status')->options(['active' => 'Active', 'disabled' => 'Disabled', 'archived' => 'Archived'])->default('active'),
            Textarea::make('system_prompt')->rows(4),
            Textarea::make('user_prompt_template')->rows(6)->required(),
            Textarea::make('variables_json')->json()->rows(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->searchable()->sortable(),
            TextColumn::make('name')->searchable(),
            TextColumn::make('purpose')->badge(),
            TextColumn::make('status')->badge(),
        ])->headerActions([CreateAction::make()])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageAiPromptTemplates::route('/')];
    }
}
