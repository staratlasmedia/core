<?php

namespace App\Filament\Resources\AuthorizationCodeResource\Pages;

use App\Filament\Resources\AuthorizationCodeResource;
use Filament\Resources\Pages\ManageRecords;

class ManageAuthorizationCodes extends ManageRecords
{
    protected static string $resource = AuthorizationCodeResource::class;
}
