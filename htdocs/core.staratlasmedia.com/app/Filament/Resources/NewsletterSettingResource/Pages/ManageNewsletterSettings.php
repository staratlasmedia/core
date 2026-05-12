<?php

namespace App\Filament\Resources\NewsletterSettingResource\Pages;

use App\Filament\Resources\NewsletterSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageNewsletterSettings extends ManageRecords
{
    protected static string $resource = NewsletterSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
