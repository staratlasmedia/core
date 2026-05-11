<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsletterEvent extends CoreModel
{
    public const UPDATED_AT = null;

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

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(NewsletterSubscriber::class, 'newsletter_subscriber_id');
    }

    public function list(): BelongsTo
    {
        return $this->belongsTo(NewsletterList::class, 'newsletter_list_id');
    }
}
