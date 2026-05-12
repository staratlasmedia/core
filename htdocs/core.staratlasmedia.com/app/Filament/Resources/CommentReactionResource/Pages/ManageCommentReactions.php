<?php

namespace App\Filament\Resources\CommentReactionResource\Pages;

use App\Filament\Resources\CommentReactionResource;
use Filament\Resources\Pages\ManageRecords;

class ManageCommentReactions extends ManageRecords
{
    protected static string $resource = CommentReactionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
