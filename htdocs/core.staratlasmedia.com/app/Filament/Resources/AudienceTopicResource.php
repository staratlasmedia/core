<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AudienceTopicResource\Pages\ManageAudienceTopics;
use App\Models\AudienceTopic;
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

class AudienceTopicResource extends Resource
{
    protected static ?string $model = AudienceTopic::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;
    protected static string|UnitEnum|null $navigationGroup = 'Audience';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('site_id')->relationship('site', 'name')->searchable()->preload(),
            Select::make('push_group_id')->relationship('pushGroup', 'name')->searchable()->preload(),
            Select::make('parent_id')->relationship('parent', 'label')->searchable()->preload(),
            TextInput::make('type')->required(),
            TextInput::make('slug')->required(),
            TextInput::make('label')->required(),
            TextInput::make('language')->maxLength(16),
            Select::make('status')->options(['active' => 'Active', 'disabled' => 'Disabled', 'archived' => 'Archived'])->default('active'),
            TextInput::make('sort_order')->numeric()->default(0),
            Textarea::make('description'),
            Textarea::make('metadata_json')->json()->rows(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('type')->badge()->sortable(),
            TextColumn::make('slug')->searchable(),
            TextColumn::make('label')->searchable(),
            TextColumn::make('status')->badge(),
        ])->headerActions([CreateAction::make()])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageAudienceTopics::route('/')];
    }
}
