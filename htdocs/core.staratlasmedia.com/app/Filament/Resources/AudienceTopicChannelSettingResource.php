<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AudienceTopicChannelSettingResource\Pages\ManageAudienceTopicChannelSettings;
use App\Models\AudienceTopicChannelSetting;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class AudienceTopicChannelSettingResource extends Resource
{
    protected static ?string $model = AudienceTopicChannelSetting::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;
    protected static string|UnitEnum|null $navigationGroup = 'Audience';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('audience_topic_id')->relationship('topic', 'label')->searchable()->preload()->required(),
            Select::make('channel')->options(['newsletter' => 'Newsletter', 'push' => 'Push'])->required(),
            Checkbox::make('enabled')->default(true),
            Checkbox::make('visible_in_forms')->default(true),
            Checkbox::make('default_selected')->default(false),
            Checkbox::make('requires_explicit_consent')->default(true),
            TextInput::make('sort_order')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('topic.label')->label('Topic')->searchable(),
            TextColumn::make('channel')->badge(),
            IconColumn::make('enabled')->boolean(),
            IconColumn::make('visible_in_forms')->boolean(),
            IconColumn::make('requires_explicit_consent')->boolean(),
            TextColumn::make('sort_order')->sortable(),
        ])->headerActions([CreateAction::make()])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageAudienceTopicChannelSettings::route('/')];
    }
}
