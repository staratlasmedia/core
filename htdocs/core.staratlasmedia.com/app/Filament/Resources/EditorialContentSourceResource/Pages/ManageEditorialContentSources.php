<?php

namespace App\Filament\Resources\EditorialContentSourceResource\Pages;

use App\Filament\Resources\EditorialContentSourceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageEditorialContentSources extends ManageRecords
{
    protected static string $resource = EditorialContentSourceResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
