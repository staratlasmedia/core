<?php

namespace App\Filament\Resources\AuthProviderResource\Pages;

use App\Filament\Resources\AuthProviderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAuthProviders extends ManageRecords
{
    protected static string $resource = AuthProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
