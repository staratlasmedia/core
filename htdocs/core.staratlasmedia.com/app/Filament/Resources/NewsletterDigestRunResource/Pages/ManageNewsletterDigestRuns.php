<?php

namespace App\Filament\Resources\NewsletterDigestRunResource\Pages;

use App\Filament\Resources\NewsletterDigestRunResource;
use Filament\Resources\Pages\ManageRecords;

class ManageNewsletterDigestRuns extends ManageRecords
{
    protected static string $resource = NewsletterDigestRunResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
