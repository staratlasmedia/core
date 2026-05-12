<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsletterSubscriberResource\Pages\ManageNewsletterSubscribers;
use App\Models\NewsletterSubscriber;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class NewsletterSubscriberResource extends Resource
{
    protected static ?string $model = NewsletterSubscriber::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;
    protected static string|UnitEnum|null $navigationGroup = 'Newsletter Operations';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('site_id')->relationship('site', 'name')->searchable()->preload()->disabled(),
            Select::make('push_group_id')->relationship('pushGroup', 'name')->searchable()->preload()->disabled(),
            TextInput::make('uuid')->disabled(),
            TextInput::make('normalized_email_hash')->disabled(),
            Select::make('status')->options([
                'pending' => 'Pending',
                'subscribed' => 'Subscribed',
                'unsubscribed' => 'Unsubscribed',
                'suppressed' => 'Suppressed',
                'bounced' => 'Bounced',
                'complained' => 'Complained',
            ])->required(),
            TextInput::make('language')->maxLength(16),
            TextInput::make('source_type')->disabled(),
            TextInput::make('source_url')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('uuid')->searchable()->toggleable(),
            TextColumn::make('site.code')->label('Site')->sortable(),
            TextColumn::make('status')->badge()->sortable(),
            TextColumn::make('language')->sortable(),
            TextColumn::make('source_type')->badge(),
            TextColumn::make('subscribed_at')->dateTime()->sortable(),
            TextColumn::make('unsubscribed_at')->dateTime()->sortable(),
        ])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageNewsletterSubscribers::route('/')];
    }
}
