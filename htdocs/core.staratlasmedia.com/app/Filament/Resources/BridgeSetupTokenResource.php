<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BridgeSetupTokenResource\Pages\ManageBridgeSetupTokens;
use App\Models\BridgeSetupToken;
use App\Models\PushGroup;
use App\Models\Site;
use App\Models\SiteOrigin;
use App\Services\Bridge\BridgeConfigBuilder;
use App\Services\Bridge\BridgeTokenFactory;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use UnitEnum;

class BridgeSetupTokenResource extends Resource
{
    protected static ?string $model = BridgeSetupToken::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?string $navigationLabel = 'Bridge Setup Tokens';

    protected static string|UnitEnum|null $navigationGroup = 'WordPress Bridge';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('site_id')->relationship('site', 'name')->searchable()->preload()->required(),
            Select::make('push_group_id')->relationship('pushGroup', 'name')->searchable()->preload(),
            Select::make('site_origin_id')->relationship('siteOrigin', 'origin')->searchable()->preload(),
            TextInput::make('intended_site_code')->required(),
            TextInput::make('intended_push_group_code'),
            TextInput::make('intended_language')->maxLength(16),
            TextInput::make('intended_section'),
            TextInput::make('intended_origin')->url(),
            TextInput::make('intended_base_path'),
            Select::make('status')
                ->options([
                    'active' => 'Active',
                    'consumed' => 'Consumed',
                    'expired' => 'Expired',
                    'revoked' => 'Revoked',
                ])
                ->required(),
            Textarea::make('metadata_json')->json()->rows(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('uuid')->searchable()->toggleable(),
                TextColumn::make('site.code')->label('Site')->sortable()->searchable(),
                TextColumn::make('pushGroup.code')->label('Push Group')->sortable(),
                TextColumn::make('intended_origin')->searchable(),
                TextColumn::make('intended_base_path')->label('Base Path'),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('expires_at')->dateTime()->sortable(),
                TextColumn::make('consumedByInstallation.uuid')->label('Installation')->toggleable(),
            ])
            ->headerActions([
                Action::make('generateSetupToken')
                    ->label('Generate setup token')
                    ->icon(Heroicon::OutlinedPlus)
                    ->form([
                        Select::make('site_id')
                            ->label('Site')
                            ->options(fn (): array => Site::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->required()
                            ->searchable(),
                        Select::make('push_group_id')
                            ->label('Push Group')
                            ->options(fn (): array => PushGroup::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable(),
                        Select::make('site_origin_id')
                            ->label('Site Origin')
                            ->options(fn (): array => SiteOrigin::query()->orderBy('origin')->get()->mapWithKeys(
                                fn (SiteOrigin $origin): array => [$origin->id => $origin->origin.' '.($origin->path_prefix ?? '/')]
                            )->all())
                            ->searchable(),
                        TextInput::make('section')->default('main'),
                    ])
                    ->action(function (array $data): void {
                        $site = Site::query()->findOrFail($data['site_id']);
                        $pushGroup = isset($data['push_group_id']) ? PushGroup::query()->find($data['push_group_id']) : null;
                        $siteOrigin = isset($data['site_origin_id']) ? SiteOrigin::query()->find($data['site_origin_id']) : null;
                        $result = app(BridgeTokenFactory::class)->create($site, $pushGroup, $siteOrigin, $data['section'] ?? null, auth()->id());

                        Notification::make()
                            ->title('Copy this setup token now')
                            ->body($result['token'])
                            ->warning()
                            ->persistent()
                            ->send();
                    }),
            ])
            ->recordActions([
                Action::make('previewConfig')
                    ->label('Preview config')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->modalSubmitAction(false)
                    ->modalContent(fn (BridgeSetupToken $record): HtmlString => self::codePreview(app(BridgeConfigBuilder::class)->previewForToken($record))),
                Action::make('revoke')
                    ->requiresConfirmation()
                    ->icon(Heroicon::OutlinedNoSymbol)
                    ->visible(fn (BridgeSetupToken $record): bool => $record->status === 'active')
                    ->action(fn (BridgeSetupToken $record): bool => $record->forceFill([
                        'status' => 'revoked',
                        'revoked_at' => now(),
                    ])->save()),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageBridgeSetupTokens::route('/'),
        ];
    }

    /**
     * @param  array<string, mixed>  $code
     */
    private static function codePreview(array $code): HtmlString
    {
        return new HtmlString('<pre class="overflow-auto rounded-lg bg-gray-950 p-4 text-xs leading-5 text-gray-100">'.e(json_encode($code, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)).'</pre>');
    }
}
