<?php

namespace App\Filament\Resources\AuthProviderSiteSettingResource\Pages;

use App\Filament\Resources\AuthProviderSiteSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAuthProviderSiteSettings extends ManageRecords
{
    protected static string $resource = AuthProviderSiteSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
