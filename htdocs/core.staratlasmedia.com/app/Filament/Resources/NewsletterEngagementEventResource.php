<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsletterEngagementEventResource\Pages\ManageNewsletterEngagementEvents;
use App\Models\NewsletterEngagementEvent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class NewsletterEngagementEventResource extends Resource
{
    protected static ?string $model = NewsletterEngagementEvent::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelopeOpen;
    protected static string|UnitEnum|null $navigationGroup = 'Newsletter Operations';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('uuid')->toggleable(),
            TextColumn::make('event_type')->badge()->sortable(),
            TextColumn::make('newsletter_campaign_id')->label('Campaign ID')->sortable(),
            TextColumn::make('url_hash')->limit(16)->toggleable(),
            TextColumn::make('occurred_at')->dateTime()->sortable(),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageNewsletterEngagementEvents::route('/')];
    }
}
