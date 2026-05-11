<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NewsletterSubscriber extends CoreModel
{
    protected $hidden = [
        'email_hash',
        'email_encrypted',
    ];

    protected function casts(): array
    {
        return [
            'email_encrypted' => 'encrypted',
            'subscribed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lists(): BelongsToMany
    {
        return $this->belongsToMany(NewsletterList::class, 'newsletter_list_subscriber')
            ->withPivot(['status', 'subscribed_at', 'unsubscribed_at'])
            ->withTimestamps();
    }

    public function events(): HasMany
    {
        return $this->hasMany(NewsletterEvent::class);
    }
}
