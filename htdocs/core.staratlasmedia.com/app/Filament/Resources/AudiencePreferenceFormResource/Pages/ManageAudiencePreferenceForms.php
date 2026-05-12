<?php

namespace App\Filament\Resources\AudiencePreferenceFormResource\Pages;

use App\Filament\Resources\AudiencePreferenceFormResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAudiencePreferenceForms extends ManageRecords
{
    protected static string $resource = AudiencePreferenceFormResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
