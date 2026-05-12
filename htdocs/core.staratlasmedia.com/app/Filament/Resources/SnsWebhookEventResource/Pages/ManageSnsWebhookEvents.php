<?php

namespace App\Filament\Resources\SnsWebhookEventResource\Pages;

use App\Filament\Resources\SnsWebhookEventResource;
use Filament\Resources\Pages\ManageRecords;

class ManageSnsWebhookEvents extends ManageRecords
{
    protected static string $resource = SnsWebhookEventResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
