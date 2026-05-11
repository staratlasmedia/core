<?php

namespace App\Filament\Resources\VapidKeySetResource\Pages;

use App\Filament\Resources\VapidKeySetResource;
use Filament\Resources\Pages\ManageRecords;

class ManageVapidKeySets extends ManageRecords
{
    protected static string $resource = VapidKeySetResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
