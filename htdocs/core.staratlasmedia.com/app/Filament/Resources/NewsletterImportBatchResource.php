<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsletterImportBatchResource\Pages\ManageNewsletterImportBatches;
use App\Models\NewsletterImportBatch;
use App\Services\Newsletter\NewsletterCsvImportService;
use App\Services\Newsletter\Exceptions\NewsletterOperationBlocked;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class NewsletterImportBatchResource extends Resource
{
    protected static ?string $model = NewsletterImportBatch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static string|UnitEnum|null $navigationGroup = 'Newsletter';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('site_id')->relationship('site', 'name')->searchable()->preload(),
            Select::make('push_group_id')->relationship('pushGroup', 'name')->searchable()->preload(),
            Select::make('newsletter_list_id')->relationship('list', 'name')->searchable()->preload(),
            Select::make('source_type')->options(['csv' => 'CSV'])->default('csv')->required(),
            FileUpload::make('storage_path')->disk('local')->directory('newsletter-imports')->acceptedFileTypes(['text/csv', 'text/plain']),
            Textarea::make('mapping_json')->json()->default('{"email":"email","topic_slugs":"topics"}'),
            Textarea::make('options_json')->json()->default('{"respect_suppression":true,"attach_to_list":true,"default_status":"subscribed"}'),
            Textarea::make('dry_run_report_json')->json()->rows(6)->disabled(),
            Textarea::make('commit_report_json')->json()->rows(4)->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('uuid')->searchable()->toggleable(),
                TextColumn::make('original_filename')->searchable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('total_rows')->sortable(),
                TextColumn::make('valid_rows')->sortable(),
                TextColumn::make('duplicate_rows')->sortable(),
                TextColumn::make('suppressed_rows')->sortable(),
                TextColumn::make('imported_rows')->sortable(),
                TextColumn::make('committed_at')->dateTime()->sortable(),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([
                EditAction::make(),
                Action::make('dry_run')
                    ->label('Dry-run')
                    ->action(function (NewsletterImportBatch $record): void {
                        if (! $record->storage_path || ! Storage::disk('local')->exists($record->storage_path)) {
                            Notification::make()->title('Missing CSV file')->danger()->send();

                            return;
                        }

                        $report = app(NewsletterCsvImportService::class)->dryRun(
                            $record,
                            Storage::disk('local')->get($record->storage_path),
                            $record->mapping_json ?? ['email' => 'email'],
                            $record->options_json ?? ['respect_suppression' => true],
                        );
                        Notification::make()->title('Dry-run valid rows: '.$report['valid_rows'])->send();
                    }),
                Action::make('commit')
                    ->label('Commit import')
                    ->requiresConfirmation()
                    ->action(function (NewsletterImportBatch $record): void {
                        try {
                            $stats = app(NewsletterCsvImportService::class)->commit($record, auth()->id());
                            Notification::make()->title('Imported rows: '.$stats['imported_rows'].'; updated rows: '.$stats['updated_rows'])->send();
                        } catch (NewsletterOperationBlocked $exception) {
                            Notification::make()->title('Import blocked: '.$exception->getMessage())->danger()->send();
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageNewsletterImportBatches::route('/')];
    }
}
