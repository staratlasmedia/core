<?php

namespace App\Filament\Resources\NewsletterTemplateResource\Pages;

use App\Filament\Resources\NewsletterTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageNewsletterTemplates extends ManageRecords
{
    protected static string $resource = NewsletterTemplateResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
