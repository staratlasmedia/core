<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PushCampaign extends CoreModel
{
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'metadata' => 'array',
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function targets(): HasMany
    {
        return $this->hasMany(PushCampaignTarget::class);
    }

    public function deliveryLogs(): HasMany
    {
        return $this->hasMany(PushDeliveryLog::class);
    }
}
