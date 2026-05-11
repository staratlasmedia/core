<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PushSubscriptionResource\Pages\ManagePushSubscriptions;
use App\Models\PushSubscription;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class PushSubscriptionResource extends Resource
{
    protected static ?string $model = PushSubscription::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDevicePhoneMobile;

    protected static ?string $navigationLabel = 'Push Subscriptions';

    protected static string|UnitEnum|null $navigationGroup = 'Push';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('site.name')->label('Site')->disabled(),
            TextInput::make('source')->disabled(),
            TextInput::make('status')->disabled(),
            TextInput::make('pushGroup.code')->label('Push Group')->disabled(),
            TextInput::make('origin')->disabled(),
            TextInput::make('service_worker_url')->disabled(),
            TextInput::make('service_worker_scope')->disabled(),
            TextInput::make('language')->disabled(),
            TextInput::make('section')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('site.name')->searchable()->sortable(),
                TextColumn::make('source')->badge()->sortable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('pushGroup.code')->label('Push Group')->sortable(),
                TextColumn::make('origin')->searchable(),
                TextColumn::make('service_worker_scope')->label('Scope'),
                TextColumn::make('language')->sortable(),
                TextColumn::make('section')->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePushSubscriptions::route('/'),
        ];
    }
}
