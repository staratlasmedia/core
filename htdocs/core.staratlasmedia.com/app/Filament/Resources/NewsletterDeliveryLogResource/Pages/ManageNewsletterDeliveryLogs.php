<?php

namespace App\Filament\Resources\NewsletterDeliveryLogResource\Pages;

use App\Filament\Resources\NewsletterDeliveryLogResource;
use Filament\Resources\Pages\ManageRecords;

class ManageNewsletterDeliveryLogs extends ManageRecords
{
    protected static string $resource = NewsletterDeliveryLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
