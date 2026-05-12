<?php

namespace App\Filament\Resources\NewsletterEngagementEventResource\Pages;

use App\Filament\Resources\NewsletterEngagementEventResource;
use Filament\Resources\Pages\ManageRecords;

class ManageNewsletterEngagementEvents extends ManageRecords
{
    protected static string $resource = NewsletterEngagementEventResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
