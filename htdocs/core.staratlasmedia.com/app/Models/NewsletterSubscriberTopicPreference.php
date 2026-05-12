<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsletterSubscriberTopicPreference extends CoreModel
{
    protected function casts(): array
    {
        return [
            'consented_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
            'metadata_json' => 'array',
        ];
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(NewsletterSubscriber::class, 'newsletter_subscriber_id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(AudienceTopic::class, 'audience_topic_id');
    }
}
