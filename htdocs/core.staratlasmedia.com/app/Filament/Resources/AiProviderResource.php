<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AiProviderResource\Pages\ManageAiProviders;
use App\Models\AiProvider;
use App\Services\Ai\AiGenerationService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class AiProviderResource extends Resource
{
    protected static ?string $model = AiProvider::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCpuChip;

    protected static string|UnitEnum|null $navigationGroup = 'AI';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')->required()->unique(ignoreRecord: true),
            TextInput::make('name')->required(),
            Select::make('provider_type')->options([
                'openai' => 'OpenAI',
                'deepseek' => 'DeepSeek',
                'groq' => 'Groq',
                'openai_compatible' => 'OpenAI-compatible',
                'custom' => 'Custom',
            ])->required(),
            Select::make('status')->options(['disabled' => 'Disabled', 'enabled' => 'Enabled'])->default('disabled'),
            TextInput::make('base_url')->url(),
            TextInput::make('api_key_encrypted')->label('API key')->password()->helperText('Leave blank to keep the stored encrypted value.')->formatStateUsing(fn () => null)->dehydrated(fn ($state) => filled($state)),
            TextInput::make('organization_id'),
            TextInput::make('project_id'),
            TextInput::make('default_model'),
            TextInput::make('default_temperature')->numeric(),
            TextInput::make('default_max_tokens')->numeric(),
            Checkbox::make('cost_tracking_enabled'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->searchable()->sortable(),
                TextColumn::make('provider_type')->badge(),
                TextColumn::make('default_model')->searchable(),
                IconColumn::make('cost_tracking_enabled')->boolean(),
                TextColumn::make('status')->badge(),
                TextColumn::make('last_tested_at')->dateTime()->sortable(),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([
                EditAction::make(),
                Action::make('test_provider')
                    ->label('Test provider')
                    ->action(function (AiProvider $record): void {
                        $result = app(AiGenerationService::class)->testProvider($record);
                        Notification::make()->title('AI test: '.$result['status'])->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageAiProviders::route('/')];
    }
}
