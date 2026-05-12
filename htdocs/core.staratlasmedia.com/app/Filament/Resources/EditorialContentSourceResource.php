<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EditorialContentSourceResource\Pages\ManageEditorialContentSources;
use App\Models\EditorialContentSource;
use App\Services\Editorial\EditorialContentIngestService;
use App\Services\Newsletter\Exceptions\NewsletterOperationBlocked;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class EditorialContentSourceResource extends Resource
{
    protected static ?string $model = EditorialContentSource::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;
    protected static string|UnitEnum|null $navigationGroup = 'Editorial Content';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('site_id')->relationship('site', 'name')->searchable()->preload(),
            Select::make('push_group_id')->relationship('pushGroup', 'name')->searchable()->preload(),
            Select::make('type')->options(['wordpress_rest' => 'WordPress REST', 'rss' => 'RSS', 'atom' => 'Atom', 'manual' => 'Manual'])->required(),
            TextInput::make('code')->required()->unique(ignoreRecord: true),
            TextInput::make('name')->required(),
            TextInput::make('url')->url(),
            TextInput::make('api_base_url')->url(),
            TextInput::make('language')->maxLength(16),
            TextInput::make('section'),
            Select::make('status')->options(['active' => 'Active', 'disabled' => 'Disabled', 'error' => 'Error'])->default('disabled'),
            Checkbox::make('polling_enabled')->helperText('Keep disabled for Phase 9 production unless explicitly approved.'),
            TextInput::make('polling_interval_minutes')->numeric(),
            Textarea::make('metadata_json')->json()->rows(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->searchable(),
            TextColumn::make('type')->badge(),
            TextColumn::make('status')->badge(),
            IconColumn::make('polling_enabled')->boolean(),
            TextColumn::make('last_successful_poll_at')->dateTime()->sortable(),
        ])->headerActions([CreateAction::make()])->recordActions([
            EditAction::make(),
            Action::make('test_fetch')->label('Preview fetch')->action(function (EditorialContentSource $record): void {
                $result = app(EditorialContentIngestService::class)->testFetch($record, (int) config('core.newsletter.test_fetch_limit', 10), false);
                Notification::make()->title('Preview '.$result['status'].' - '.count($result['items'] ?? []).' items; no items persisted')->send();
            }),
            Action::make('persist_fetch')->label('Persist bounded fetch')->requiresConfirmation()->action(function (EditorialContentSource $record): void {
                try {
                    $result = app(EditorialContentIngestService::class)->testFetch($record, (int) config('core.newsletter.test_fetch_limit', 10), true);
                    Notification::make()->title('Persist '.$result['status'].' - '.count($result['items'] ?? []).' items')->send();
                } catch (NewsletterOperationBlocked $exception) {
                    Notification::make()->title('Fetch blocked: '.$exception->getMessage())->danger()->send();
                }
            }),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageEditorialContentSources::route('/')];
    }
}
