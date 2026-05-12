<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailSenderIdentityResource\Pages\ManageEmailSenderIdentities;
use App\Models\EmailSenderIdentity;
use App\Services\Newsletter\NewsletterSesTestService;
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

class EmailSenderIdentityResource extends Resource
{
    protected static ?string $model = EmailSenderIdentity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    protected static string|UnitEnum|null $navigationGroup = 'Newsletter';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('site_id')->relationship('site', 'name')->searchable()->preload(),
            TextInput::make('code')->required()->unique(ignoreRecord: true),
            TextInput::make('from_name')->required(),
            TextInput::make('from_email')->email()->required(),
            TextInput::make('reply_to')->email(),
            TextInput::make('region'),
            TextInput::make('ses_configuration_set'),
            TextInput::make('aws_access_key_id_encrypted')->label('AWS access key ID')->password()->helperText('Leave blank to keep the stored encrypted value.')->formatStateUsing(fn () => null)->dehydrated(fn ($state) => filled($state)),
            TextInput::make('aws_secret_access_key_encrypted')->label('AWS secret access key')->password()->helperText('Leave blank to keep the stored encrypted value.')->formatStateUsing(fn () => null)->dehydrated(fn ($state) => filled($state)),
            Checkbox::make('send_enabled'),
            Checkbox::make('test_send_enabled'),
            TextInput::make('max_send_rate_per_minute')->numeric(),
            Select::make('status')->options(['disabled' => 'Disabled', 'active' => 'Active', 'unverified' => 'Unverified'])->default('disabled'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->searchable()->sortable(),
                TextColumn::make('from_email')->searchable(),
                TextColumn::make('region')->sortable(),
                IconColumn::make('send_enabled')->boolean(),
                IconColumn::make('test_send_enabled')->boolean(),
                TextColumn::make('status')->badge(),
                TextColumn::make('last_tested_at')->dateTime()->sortable(),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([
                EditAction::make(),
                Action::make('send_test')
                    ->label('Send test')
                    ->form([TextInput::make('recipient')->email()->required()])
                    ->action(function (EmailSenderIdentity $record, array $data): void {
                        $result = app(NewsletterSesTestService::class)->sendControlledTest($record, $data['recipient']);
                        Notification::make()->title('Controlled test: '.$result['status'])->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageEmailSenderIdentities::route('/')];
    }
}
