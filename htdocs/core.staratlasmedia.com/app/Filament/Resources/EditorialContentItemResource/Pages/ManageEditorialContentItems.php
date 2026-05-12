<?php

namespace App\Filament\Resources\EditorialContentItemResource\Pages;

use App\Filament\Resources\EditorialContentItemResource;
use Filament\Resources\Pages\ManageRecords;

class ManageEditorialContentItems extends ManageRecords
{
    protected static string $resource = EditorialContentItemResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
