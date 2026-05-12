<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsletterDeliveryLogResource\Pages\ManageNewsletterDeliveryLogs;
use App\Models\NewsletterDeliveryLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class NewsletterDeliveryLogResource extends Resource
{
    protected static ?string $model = NewsletterDeliveryLog::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;
    protected static string|UnitEnum|null $navigationGroup = 'Newsletter Operations';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('uuid')->toggleable(),
            TextColumn::make('newsletter_campaign_id')->label('Campaign ID')->sortable(),
            TextColumn::make('status')->badge()->sortable(),
            TextColumn::make('provider')->badge(),
            TextColumn::make('ses_message_id')->searchable()->toggleable(),
            TextColumn::make('open_count')->sortable(),
            TextColumn::make('click_count')->sortable(),
            TextColumn::make('sent_at')->dateTime()->sortable(),
            TextColumn::make('delivered_at')->dateTime()->sortable(),
            TextColumn::make('bounced_at')->dateTime()->sortable(),
            TextColumn::make('complained_at')->dateTime()->sortable(),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageNewsletterDeliveryLogs::route('/')];
    }
}
