<?php

namespace App\Filament\Resources\CommentModerationEventResource\Pages;

use App\Filament\Resources\CommentModerationEventResource;
use Filament\Resources\Pages\ManageRecords;

class ManageCommentModerationEvents extends ManageRecords
{
    protected static string $resource = CommentModerationEventResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
