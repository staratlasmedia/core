<?php

namespace App\Filament\Resources\AiGenerationJobResource\Pages;

use App\Filament\Resources\AiGenerationJobResource;
use Filament\Resources\Pages\ManageRecords;

class ManageAiGenerationJobs extends ManageRecords
{
    protected static string $resource = AiGenerationJobResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
