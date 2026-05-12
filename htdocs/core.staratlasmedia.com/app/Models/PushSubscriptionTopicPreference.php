<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushSubscriptionTopicPreference extends CoreModel
{
    protected function casts(): array
    {
        return [
            'consented_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
            'metadata_json' => 'array',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(PushSubscription::class, 'push_subscription_id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(AudienceTopic::class, 'audience_topic_id');
    }
}
