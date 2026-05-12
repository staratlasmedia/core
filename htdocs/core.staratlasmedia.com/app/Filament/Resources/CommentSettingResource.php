<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommentSettingResource\Pages\ManageCommentSettings;
use App\Models\CommentSetting;
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

class CommentSettingResource extends Resource
{
    protected static ?string $model = CommentSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static ?string $navigationLabel = 'Settings';

    protected static string|UnitEnum|null $navigationGroup = 'Comments';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('scope')
                ->options([
                    CommentSetting::SCOPE_GLOBAL => 'Global',
                    CommentSetting::SCOPE_SITE => 'Site',
                    CommentSetting::SCOPE_PUSH_GROUP => 'Push Group',
                    CommentSetting::SCOPE_BRIDGE_INSTALLATION => 'Bridge Installation',
                ])
                ->required(),
            TextInput::make('scope_key')
                ->helperText('Deterministic values: global, site:{id}, push_group:{id}, bridge_installation:{id}.')
                ->required()
                ->unique(ignoreRecord: true),
            Select::make('site_id')->relationship('site', 'name')->searchable()->preload(),
            Select::make('push_group_id')->relationship('pushGroup', 'name')->searchable()->preload(),
            Select::make('bridge_installation_id')->relationship('bridgeInstallation', 'uuid')->searchable()->preload(),
            Checkbox::make('comments_enabled'),
            Checkbox::make('require_login')->default(true),
            Checkbox::make('allow_guest'),
            Checkbox::make('require_moderation')->default(true),
            Checkbox::make('auto_approve_trusted_users'),
            TextInput::make('max_depth')->numeric()->default(3),
            TextInput::make('max_length')->numeric()->default(2000),
            TextInput::make('min_length')->numeric()->default(2),
            TextInput::make('default_sort'),
            TextInput::make('close_after_days')->numeric(),
            Textarea::make('rate_limit_json')->json()->rows(3),
            Textarea::make('banned_words_json')->json()->rows(3),
            Textarea::make('moderation_rules_json')->json()->rows(3),
            Checkbox::make('notify_moderators'),
            Textarea::make('metadata_json')->json()->rows(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('scope')->badge()->sortable(),
                TextColumn::make('scope_key')->searchable()->sortable(),
                IconColumn::make('comments_enabled')->boolean()->label('Enabled'),
                IconColumn::make('require_login')->boolean()->label('Login'),
                IconColumn::make('allow_guest')->boolean()->label('Guests'),
                IconColumn::make('require_moderation')->boolean()->label('Moderation'),
                TextColumn::make('max_depth')->sortable(),
                TextColumn::make('max_length')->sortable(),
                TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCommentSettings::route('/'),
        ];
    }
}
