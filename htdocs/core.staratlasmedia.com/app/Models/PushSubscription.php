<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PushSubscription extends CoreModel
{
    protected $hidden = [
        'endpoint_hash',
        'endpoint_encrypted',
        'p256dh_encrypted',
        'auth_encrypted',
    ];

    protected function casts(): array
    {
        return [
            'endpoint_encrypted' => 'encrypted',
            'p256dh_encrypted' => 'encrypted',
            'auth_encrypted' => 'encrypted',
            'created_at_legacy' => 'datetime',
            'last_active_at_legacy' => 'datetime',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function siteOrigin(): BelongsTo
    {
        return $this->belongsTo(SiteOrigin::class);
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(PushSubscriber::class, 'push_subscriber_id');
    }

    public function legacyPushApp(): BelongsTo
    {
        return $this->belongsTo(LegacyPushApp::class);
    }

    public function vapidKeySet(): BelongsTo
    {
        return $this->belongsTo(VapidKeySet::class);
    }

    public function supersededBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'superseded_by_subscription_id');
    }

    public function contexts(): HasMany
    {
        return $this->hasMany(PushSubscriptionContext::class);
    }

    public function deliveryLogs(): HasMany
    {
        return $this->hasMany(PushDeliveryLog::class);
    }

    public function topics(): BelongsToMany
    {
        return $this->belongsToMany(PushTopic::class, 'push_subscription_topics')->withTimestamps();
    }

    public function scopeModernDispatchEligible(Builder $query): Builder
    {
        return $query
            ->whereIn('source', ['core_sdk', 'core_reconfirmed'])
            ->whereIn('status', ['active', 'core_reconfirmed']);
    }
}
