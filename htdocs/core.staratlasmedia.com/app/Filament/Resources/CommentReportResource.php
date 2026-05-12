<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommentReportResource\Pages\ManageCommentReports;
use App\Models\CommentReport;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class CommentReportResource extends Resource
{
    protected static ?string $model = CommentReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    protected static ?string $navigationLabel = 'Reports';

    protected static string|UnitEnum|null $navigationGroup = 'Comments';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('status')
                ->options([
                    CommentReport::STATUS_OPEN => 'Open',
                    CommentReport::STATUS_REVIEWED => 'Reviewed',
                    CommentReport::STATUS_DISMISSED => 'Dismissed',
                    CommentReport::STATUS_ACTIONED => 'Actioned',
                ])
                ->required(),
            Textarea::make('reason')->rows(2),
            Textarea::make('message')->rows(4),
            Textarea::make('metadata_json')->json()->rows(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('site.code')->label('Site')->sortable(),
                TextColumn::make('comment.uuid')->label('Comment')->searchable(),
                TextColumn::make('reason')->badge()->sortable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('message')->limit(56),
                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('reviewed_at')->dateTime()->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCommentReports::route('/'),
        ];
    }
}
