<?php

namespace App\Filament\Resources\EmailSenderIdentityResource\Pages;

use App\Filament\Resources\EmailSenderIdentityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageEmailSenderIdentities extends ManageRecords
{
    protected static string $resource = EmailSenderIdentityResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
