<?php

namespace App\Filament\Resources\NewsletterDigestRecipeResource\Pages;

use App\Filament\Resources\NewsletterDigestRecipeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageNewsletterDigestRecipes extends ManageRecords
{
    protected static string $resource = NewsletterDigestRecipeResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
