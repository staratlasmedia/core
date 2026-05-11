<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NewsletterList extends CoreModel
{
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(NewsletterSubscriber::class, 'newsletter_list_subscriber')
            ->withPivot(['status', 'subscribed_at', 'unsubscribed_at'])
            ->withTimestamps();
    }

    public function events(): HasMany
    {
        return $this->hasMany(NewsletterEvent::class);
    }
}
