<?php

namespace App\Filament\Resources\AudienceTopicChannelSettingResource\Pages;

use App\Filament\Resources\AudienceTopicChannelSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAudienceTopicChannelSettings extends ManageRecords
{
    protected static string $resource = AudienceTopicChannelSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
