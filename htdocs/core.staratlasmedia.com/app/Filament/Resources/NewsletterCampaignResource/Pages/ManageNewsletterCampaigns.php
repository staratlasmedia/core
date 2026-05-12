<?php

namespace App\Filament\Resources\NewsletterCampaignResource\Pages;

use App\Filament\Resources\NewsletterCampaignResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageNewsletterCampaigns extends ManageRecords
{
    protected static string $resource = NewsletterCampaignResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
