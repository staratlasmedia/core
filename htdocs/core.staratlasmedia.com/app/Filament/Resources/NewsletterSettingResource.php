<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsletterSettingResource\Pages\ManageNewsletterSettings;
use App\Models\NewsletterSetting;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class NewsletterSettingResource extends Resource
{
    protected static ?string $model = NewsletterSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static string|UnitEnum|null $navigationGroup = 'Newsletter';

    protected static ?string $navigationLabel = 'Settings';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('scope')->options([
                NewsletterSetting::SCOPE_GLOBAL => 'Global',
                NewsletterSetting::SCOPE_SITE => 'Site',
                NewsletterSetting::SCOPE_PUSH_GROUP => 'Push Group',
                NewsletterSetting::SCOPE_BRIDGE_INSTALLATION => 'Bridge Installation',
            ])->required(),
            TextInput::make('scope_key')->helperText('global, site:{id}, push_group:{id}, bridge_installation:{id}.')->required()->unique(ignoreRecord: true),
            Select::make('site_id')->relationship('site', 'name')->searchable()->preload(),
            Select::make('push_group_id')->relationship('pushGroup', 'name')->searchable()->preload(),
            Select::make('bridge_installation_id')->relationship('bridgeInstallation', 'uuid')->searchable()->preload(),
            Checkbox::make('newsletter_enabled'),
            Checkbox::make('double_opt_in')->default(true),
            Checkbox::make('require_consent')->default(true),
            Checkbox::make('send_enabled'),
            Checkbox::make('allow_import'),
            Checkbox::make('ai_generation_enabled'),
            Checkbox::make('rss_import_enabled'),
            Checkbox::make('wordpress_api_import_enabled'),
            Checkbox::make('automatic_digest_enabled'),
            Select::make('default_list_id')->relationship('defaultList', 'name')->searchable()->preload(),
            Select::make('default_sender_identity_id')->relationship('defaultSenderIdentity', 'code')->searchable()->preload(),
            TextInput::make('default_language')->maxLength(16),
            TextInput::make('max_send_rate_per_minute')->numeric(),
            Textarea::make('rate_limit_json')->json()->rows(3),
            Textarea::make('editorial_workflow_json')->json()->rows(3),
            Textarea::make('metadata_json')->json()->rows(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('scope')->badge()->sortable(),
                TextColumn::make('scope_key')->searchable()->sortable(),
                IconColumn::make('newsletter_enabled')->boolean()->label('Enabled'),
                IconColumn::make('send_enabled')->boolean()->label('Send'),
                IconColumn::make('allow_import')->boolean()->label('Import'),
                IconColumn::make('ai_generation_enabled')->boolean()->label('AI'),
                IconColumn::make('automatic_digest_enabled')->boolean()->label('Auto digest'),
                TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageNewsletterSettings::route('/')];
    }
}
