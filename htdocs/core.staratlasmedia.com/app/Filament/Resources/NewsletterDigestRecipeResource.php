<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsletterDigestRecipeResource\Pages\ManageNewsletterDigestRecipes;
use App\Models\NewsletterDigestRecipe;
use App\Services\Newsletter\Exceptions\NewsletterOperationBlocked;
use App\Services\Newsletter\NewsletterDigestService;
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

class NewsletterDigestRecipeResource extends Resource
{
    protected static ?string $model = NewsletterDigestRecipe::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;
    protected static string|UnitEnum|null $navigationGroup = 'Newsletter';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('site_id')->relationship('site', 'name')->searchable()->preload(),
            Select::make('push_group_id')->relationship('pushGroup', 'name')->searchable()->preload(),
            Select::make('newsletter_list_id')->relationship('list', 'name')->searchable()->preload(),
            TextInput::make('name')->required(),
            TextInput::make('code')->required()->unique(ignoreRecord: true),
            Select::make('status')->options(['paused' => 'Paused', 'active' => 'Active', 'archived' => 'Archived'])->default('paused'),
            Select::make('frequency')->options(['manual' => 'Manual', 'daily' => 'Daily', 'weekly' => 'Weekly'])->default('manual'),
            TextInput::make('language')->maxLength(16),
            TextInput::make('section'),
            Select::make('template_id')->relationship('template', 'name')->searchable()->preload(),
            Select::make('sender_identity_id')->relationship('senderIdentity', 'code')->searchable()->preload(),
            Checkbox::make('ai_enabled'),
            Checkbox::make('require_editorial_approval')->default(true),
            Checkbox::make('auto_schedule')->default(false)->disabled(),
            Checkbox::make('auto_send')->default(false)->disabled(),
            TextInput::make('max_items')->numeric()->default(5),
            TextInput::make('min_items')->numeric()->default(1),
            Textarea::make('selection_rules_json')->json()->rows(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->searchable()->sortable(),
            TextColumn::make('name')->searchable(),
            TextColumn::make('status')->badge(),
            TextColumn::make('frequency')->badge(),
            IconColumn::make('auto_send')->boolean(),
            IconColumn::make('auto_schedule')->boolean(),
            IconColumn::make('require_editorial_approval')->boolean(),
        ])->headerActions([CreateAction::make()])->recordActions([
            EditAction::make(),
            Action::make('create_draft')->label('Create draft')->requiresConfirmation()->action(function (NewsletterDigestRecipe $record): void {
                try {
                    $run = app(NewsletterDigestService::class)->createDraftRun($record);
                    Notification::make()->title('Digest draft created: run '.$run->id)->send();
                } catch (NewsletterOperationBlocked $exception) {
                    Notification::make()->title('Digest blocked: '.$exception->getMessage())->danger()->send();
                }
            }),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageNewsletterDigestRecipes::route('/')];
    }
}
