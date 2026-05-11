<?php

namespace App\Filament\Resources\PushGroupResource\Pages;

use App\Filament\Resources\PushGroupResource;
use Filament\Resources\Pages\ManageRecords;

class ManagePushGroups extends ManageRecords
{
    protected static string $resource = PushGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
