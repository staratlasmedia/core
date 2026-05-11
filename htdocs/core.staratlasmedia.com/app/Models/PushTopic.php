<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PushTopic extends CoreModel
{
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function subscriptions(): BelongsToMany
    {
        return $this->belongsToMany(PushSubscription::class, 'push_subscription_topics')->withTimestamps();
    }
}
