<?php

namespace App\Filament\Resources\AiModelProfileResource\Pages;

use App\Filament\Resources\AiModelProfileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAiModelProfiles extends ManageRecords
{
    protected static string $resource = AiModelProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
