<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SnsWebhookEventResource\Pages\ManageSnsWebhookEvents;
use App\Models\SnsWebhookEvent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class SnsWebhookEventResource extends Resource
{
    protected static ?string $model = SnsWebhookEvent::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;
    protected static string|UnitEnum|null $navigationGroup = 'Newsletter Operations';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('sns_message_id')->searchable()->limit(32),
            TextColumn::make('sns_type')->badge()->sortable(),
            TextColumn::make('status')->badge()->sortable(),
            TextColumn::make('topic_arn')->limit(48)->toggleable(),
            TextColumn::make('verified_at')->dateTime()->sortable(),
            TextColumn::make('processed_at')->dateTime()->sortable(),
            TextColumn::make('failure_reason')->limit(60),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageSnsWebhookEvents::route('/')];
    }
}
