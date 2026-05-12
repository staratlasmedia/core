<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AudiencePreferenceFormResource\Pages\ManageAudiencePreferenceForms;
use App\Models\AudiencePreferenceForm;
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

class AudiencePreferenceFormResource extends Resource
{
    protected static ?string $model = AudiencePreferenceForm::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;
    protected static string|UnitEnum|null $navigationGroup = 'Audience';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('site_id')->relationship('site', 'name')->searchable()->preload(),
            Select::make('push_group_id')->relationship('pushGroup', 'name')->searchable()->preload(),
            Select::make('channel')->options(['newsletter' => 'Newsletter', 'push' => 'Push'])->required(),
            TextInput::make('code')->required()->unique(ignoreRecord: true),
            TextInput::make('name')->required(),
            Select::make('status')->options(['active' => 'Active', 'disabled' => 'Disabled', 'archived' => 'Archived'])->default('active'),
            TextInput::make('title'),
            Textarea::make('description')->rows(3),
            TextInput::make('submit_label'),
            Checkbox::make('require_at_least_one_topic')->default(false),
            Checkbox::make('show_select_all')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->searchable()->sortable(),
            TextColumn::make('channel')->badge(),
            TextColumn::make('name')->searchable(),
            TextColumn::make('status')->badge(),
            IconColumn::make('require_at_least_one_topic')->boolean(),
        ])->headerActions([CreateAction::make()])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageAudiencePreferenceForms::route('/')];
    }
}
