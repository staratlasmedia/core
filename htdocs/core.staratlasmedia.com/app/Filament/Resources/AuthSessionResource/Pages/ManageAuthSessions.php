<?php

namespace App\Filament\Resources\AuthSessionResource\Pages;

use App\Filament\Resources\AuthSessionResource;
use Filament\Resources\Pages\ManageRecords;

class ManageAuthSessions extends ManageRecords
{
    protected static string $resource = AuthSessionResource::class;
}
