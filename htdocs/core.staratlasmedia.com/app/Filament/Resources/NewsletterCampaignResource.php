<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsletterCampaignResource\Pages\ManageNewsletterCampaigns;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterCampaignVersion;
use App\Services\Newsletter\Exceptions\NewsletterOperationBlocked;
use App\Services\Newsletter\NewsletterAiDraftService;
use BackedEnum;
use Filament\Actions\Action;
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

class NewsletterCampaignResource extends Resource
{
    protected static ?string $model = NewsletterCampaign::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelopeOpen;
    protected static string|UnitEnum|null $navigationGroup = 'Newsletter';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('site_id')->relationship('site', 'name')->searchable()->preload(),
            Select::make('newsletter_list_id')->relationship('list', 'name')->searchable()->preload(),
            Select::make('from_identity_id')->relationship('fromIdentity', 'code')->searchable()->preload(),
            Select::make('template_id')->relationship('template', 'name')->searchable()->preload(),
            TextInput::make('name')->required(),
            TextInput::make('subject')->required(),
            TextInput::make('preheader'),
            Select::make('status')->options([
                'draft' => 'Draft',
                'ai_draft' => 'AI draft',
                'editorial_review' => 'Editorial review',
                'approved' => 'Approved',
                'scheduled' => 'Scheduled',
                'paused' => 'Paused',
                'cancelled' => 'Cancelled',
                'failed' => 'Failed',
            ])->default('draft'),
            Textarea::make('html_body')->rows(8),
            Textarea::make('text_body')->rows(5),
            Textarea::make('editor_schema_json')->json()->rows(5),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable(),
            TextColumn::make('subject')->searchable()->limit(50),
            TextColumn::make('status')->badge(),
            TextColumn::make('scheduled_at')->dateTime()->sortable(),
        ])->headerActions([CreateAction::make()])->recordActions([
            EditAction::make(),
            Action::make('duplicate')
                ->requiresConfirmation()
                ->action(function (NewsletterCampaign $record): void {
                    $copy = $record->replicate(['uuid', 'scheduled_at', 'started_at', 'finished_at', 'approved_by', 'approved_at']);
                    $copy->name = $record->name.' copy';
                    $copy->status = 'draft';
                    $copy->save();
                }),
            Action::make('create_version')
                ->label('Create version')
                ->requiresConfirmation()
                ->action(function (NewsletterCampaign $record): void {
                    $version = ((int) $record->versions()->max('version')) + 1;
                    NewsletterCampaignVersion::query()->create([
                        'newsletter_campaign_id' => $record->id,
                        'version' => $version,
                        'subject' => $record->subject,
                        'preheader' => $record->preheader,
                        'html_body' => $record->html_body,
                        'text_body' => $record->text_body,
                        'editor_schema_json' => $record->editor_schema_json,
                        'created_by' => auth()->id(),
                        'change_note' => 'Manual Phase 9B version snapshot.',
                    ]);
                }),
            Action::make('ai_placeholder')
                ->label('AI draft skeleton')
                ->requiresConfirmation()
                ->action(function (NewsletterCampaign $record): void {
                    try {
                        app(NewsletterAiDraftService::class)->createPlaceholderDraft($record);
                    } catch (NewsletterOperationBlocked $exception) {
                        \Filament\Notifications\Notification::make()->title('AI draft blocked: '.$exception->getMessage())->danger()->send();
                    }
                }),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageNewsletterCampaigns::route('/')];
    }
}
