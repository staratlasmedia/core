<?php

namespace App\Filament\Resources\CommentThreadResource\Pages;

use App\Filament\Resources\CommentThreadResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCommentThreads extends ManageRecords
{
    protected static string $resource = CommentThreadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
