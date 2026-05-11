<?php

namespace App\Filament\Resources\SiteOriginResource\Pages;

use App\Filament\Resources\SiteOriginResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSiteOrigins extends ManageRecords
{
    protected static string $resource = SiteOriginResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
