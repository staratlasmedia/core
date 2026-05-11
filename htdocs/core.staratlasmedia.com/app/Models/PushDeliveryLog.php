<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushDeliveryLog extends CoreModel
{
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'attempted_at' => 'datetime',
            'delivered_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(PushCampaign::class, 'push_campaign_id');
    }

    public function pushSubscription(): BelongsTo
    {
        return $this->belongsTo(PushSubscription::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
