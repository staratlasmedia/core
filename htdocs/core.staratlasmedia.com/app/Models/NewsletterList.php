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
            'double_opt_in' => 'boolean',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(NewsletterSubscriber::class, 'newsletter_list_subscriber')
            ->withPivot(['status', 'subscribed_at', 'unsubscribed_at', 'source_url', 'metadata_json'])
            ->withTimestamps();
    }

    public function events(): HasMany
    {
        return $this->hasMany(NewsletterEvent::class);
    }

    public function pushGroup(): BelongsTo
    {
        return $this->belongsTo(PushGroup::class);
    }

    public function senderIdentity(): BelongsTo
    {
        return $this->belongsTo(EmailSenderIdentity::class, 'default_from_identity_id');
    }
}
