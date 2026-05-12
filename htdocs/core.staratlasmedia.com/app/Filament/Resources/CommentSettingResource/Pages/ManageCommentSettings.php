<?php

namespace App\Filament\Resources\CommentSettingResource\Pages;

use App\Filament\Resources\CommentSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCommentSettings extends ManageRecords
{
    protected static string $resource = CommentSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
