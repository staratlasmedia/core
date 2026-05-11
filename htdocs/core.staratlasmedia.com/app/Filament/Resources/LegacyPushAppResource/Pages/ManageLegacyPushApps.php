<?php

namespace App\Filament\Resources\LegacyPushAppResource\Pages;

use App\Filament\Resources\LegacyPushAppResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageLegacyPushApps extends ManageRecords
{
    protected static string $resource = LegacyPushAppResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
