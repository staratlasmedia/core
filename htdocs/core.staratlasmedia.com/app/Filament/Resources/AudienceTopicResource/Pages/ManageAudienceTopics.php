<?php

namespace App\Filament\Resources\AudienceTopicResource\Pages;

use App\Filament\Resources\AudienceTopicResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAudienceTopics extends ManageRecords
{
    protected static string $resource = AudienceTopicResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
