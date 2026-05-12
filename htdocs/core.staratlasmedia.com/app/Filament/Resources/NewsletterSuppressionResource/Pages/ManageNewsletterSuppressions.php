<?php

namespace App\Filament\Resources\NewsletterSuppressionResource\Pages;

use App\Filament\Resources\NewsletterSuppressionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageNewsletterSuppressions extends ManageRecords
{
    protected static string $resource = NewsletterSuppressionResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
