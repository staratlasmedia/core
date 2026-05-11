<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushSubscriptionContext extends CoreModel
{
    public const UPDATED_AT = null;

    protected $hidden = [
        'user_agent_hash',
    ];

    protected function casts(): array
    {
        return [
            'wp_terms_json' => 'array',
            'utm_json' => 'array',
        ];
    }

    public function pushSubscription(): BelongsTo
    {
        return $this->belongsTo(PushSubscription::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function siteOrigin(): BelongsTo
    {
        return $this->belongsTo(SiteOrigin::class);
    }
}
