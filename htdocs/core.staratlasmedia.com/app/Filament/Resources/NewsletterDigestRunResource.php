<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsletterDigestRunResource\Pages\ManageNewsletterDigestRuns;
use App\Models\NewsletterDigestRun;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class NewsletterDigestRunResource extends Resource
{
    protected static ?string $model = NewsletterDigestRun::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;
    protected static string|UnitEnum|null $navigationGroup = 'Newsletter Operations';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('uuid')->toggleable(),
            TextColumn::make('recipe.code')->label('Recipe')->searchable(),
            TextColumn::make('campaign.name')->label('Campaign')->limit(40),
            TextColumn::make('status')->badge()->sortable(),
            TextColumn::make('run_date')->date()->sortable(),
            TextColumn::make('started_at')->dateTime()->sortable(),
            TextColumn::make('finished_at')->dateTime()->sortable(),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageNewsletterDigestRuns::route('/')];
    }
}
