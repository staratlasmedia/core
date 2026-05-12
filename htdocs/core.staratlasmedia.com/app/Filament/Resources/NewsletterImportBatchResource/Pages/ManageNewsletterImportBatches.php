<?php

namespace App\Filament\Resources\NewsletterImportBatchResource\Pages;

use App\Filament\Resources\NewsletterImportBatchResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageNewsletterImportBatches extends ManageRecords
{
    protected static string $resource = NewsletterImportBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
