<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AiModelProfileResource\Pages\ManageAiModelProfiles;
use App\Models\AiModelProfile;
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

class AiModelProfileResource extends Resource
{
    protected static ?string $model = AiModelProfile::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCpuChip;
    protected static string|UnitEnum|null $navigationGroup = 'AI';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('ai_provider_id')->relationship('provider', 'code')->searchable()->preload()->required(),
            TextInput::make('code')->required(),
            TextInput::make('name')->required(),
            TextInput::make('model')->required(),
            TextInput::make('purpose')->default('generic'),
            TextInput::make('temperature')->numeric(),
            TextInput::make('max_tokens')->numeric(),
            Select::make('status')->options(['disabled' => 'Disabled', 'enabled' => 'Enabled'])->default('disabled'),
            Textarea::make('system_prompt')->rows(4),
            Textarea::make('response_format_json')->json()->rows(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->searchable()->sortable(),
            TextColumn::make('provider.code')->label('Provider'),
            TextColumn::make('purpose')->badge(),
            TextColumn::make('model')->searchable(),
            TextColumn::make('status')->badge(),
        ])->headerActions([CreateAction::make()])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageAiModelProfiles::route('/')];
    }
}
