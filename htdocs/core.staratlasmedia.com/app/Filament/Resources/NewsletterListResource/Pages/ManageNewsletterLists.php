<?php

namespace App\Filament\Resources\NewsletterListResource\Pages;

use App\Filament\Resources\NewsletterListResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageNewsletterLists extends ManageRecords
{
    protected static string $resource = NewsletterListResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
