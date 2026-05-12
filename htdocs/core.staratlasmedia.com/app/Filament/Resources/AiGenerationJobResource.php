<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AiGenerationJobResource\Pages\ManageAiGenerationJobs;
use App\Models\AiGenerationJob;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class AiGenerationJobResource extends Resource
{
    protected static ?string $model = AiGenerationJob::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCpuChip;
    protected static string|UnitEnum|null $navigationGroup = 'AI';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('uuid')->toggleable(),
            TextColumn::make('purpose')->badge()->sortable(),
            TextColumn::make('status')->badge()->sortable(),
            TextColumn::make('provider.code')->label('Provider'),
            TextColumn::make('total_tokens')->sortable(),
            TextColumn::make('estimated_cost')->sortable(),
            TextColumn::make('completed_at')->dateTime()->sortable(),
            TextColumn::make('failed_at')->dateTime()->sortable(),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageAiGenerationJobs::route('/')];
    }
}
