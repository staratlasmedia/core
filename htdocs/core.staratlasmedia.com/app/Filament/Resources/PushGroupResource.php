<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PushGroupResource\Pages\ManagePushGroups;
use App\Models\PushGroup;
use App\Services\Push\ManifestGenerator;
use App\Services\Push\ServiceWorkerGenerator;
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
use Illuminate\Support\HtmlString;
use UnitEnum;

class PushGroupResource extends Resource
{
    protected static ?string $model = PushGroup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Push Groups';

    protected static string|UnitEnum|null $navigationGroup = 'Push';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')->required()->maxLength(255)->unique(ignoreRecord: true),
            TextInput::make('name')->required()->maxLength(255),
            Textarea::make('description')->rows(3),
            TextInput::make('language')->maxLength(16),
            Select::make('status')
                ->options([
                    'active' => 'Active',
                    'paused' => 'Paused',
                    'archived' => 'Archived',
                ])
                ->default('active')
                ->required(),
            TextInput::make('manifest_id')->maxLength(255),
            TextInput::make('manifest_name')->maxLength(255),
            TextInput::make('manifest_short_name')->maxLength(255),
            TextInput::make('manifest_scope')->maxLength(255),
            TextInput::make('manifest_start_url')->maxLength(255),
            TextInput::make('service_worker_url')->maxLength(255),
            TextInput::make('service_worker_scope')->maxLength(255),
            TextInput::make('sw_version')->maxLength(255)->default('core-clean-v1'),
            Textarea::make('pwa_config_json')->json()->rows(8),
            Textarea::make('metadata_json')->json()->rows(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->searchable()->sortable(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('language')->sortable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('manifest_id')->searchable(),
                TextColumn::make('service_worker_url')->searchable(),
                TextColumn::make('service_worker_scope')->label('Scope'),
                TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                Action::make('previewServiceWorker')
                    ->label('Preview worker')
                    ->icon(Heroicon::OutlinedCodeBracketSquare)
                    ->modalHeading(fn (PushGroup $record): string => $record->code.' Service Worker')
                    ->modalSubmitAction(false)
                    ->modalContent(fn (PushGroup $record): HtmlString => self::codePreview(app(ServiceWorkerGenerator::class)->generate($record))),
                Action::make('downloadServiceWorker')
                    ->label('Download worker')
                    ->icon(Heroicon::OutlinedDocumentArrowDown)
                    ->action(fn (PushGroup $record) => response()->streamDownload(
                        static fn (): int|false => print app(ServiceWorkerGenerator::class)->generate($record),
                        $record->code.'-service-worker.js',
                        ['Content-Type' => 'application/javascript; charset=UTF-8'],
                    )),
                Action::make('previewManifest')
                    ->label('Preview manifest')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->modalHeading(fn (PushGroup $record): string => $record->code.' Manifest')
                    ->modalSubmitAction(false)
                    ->modalContent(fn (PushGroup $record): HtmlString => self::codePreview(app(ManifestGenerator::class)->generate($record))),
                Action::make('downloadManifest')
                    ->label('Download manifest')
                    ->icon(Heroicon::OutlinedArrowDownOnSquare)
                    ->action(fn (PushGroup $record) => response()->streamDownload(
                        static fn (): int|false => print app(ManifestGenerator::class)->generate($record),
                        $record->code.'.webmanifest',
                        ['Content-Type' => 'application/manifest+json; charset=UTF-8'],
                    )),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePushGroups::route('/'),
        ];
    }

    private static function codePreview(string $code): HtmlString
    {
        return new HtmlString('<pre class="overflow-auto rounded-lg bg-gray-950 p-4 text-xs leading-5 text-gray-100">'.e($code).'</pre>');
    }
}
