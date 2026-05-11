<?php

namespace App\Filament\Resources\PushSubscriptionResource\Pages;

use App\Filament\Resources\PushSubscriptionResource;
use Filament\Resources\Pages\ManageRecords;

class ManagePushSubscriptions extends ManageRecords
{
    protected static string $resource = PushSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
