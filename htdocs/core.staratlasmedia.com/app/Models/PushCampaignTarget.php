<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushCampaignTarget extends CoreModel
{
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(PushCampaign::class, 'push_campaign_id');
    }
}
