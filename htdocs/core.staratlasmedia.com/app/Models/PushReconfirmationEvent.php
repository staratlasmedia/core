<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushReconfirmationEvent extends CoreModel
{
    public const UPDATED_AT = null;

    protected $hidden = [
        'user_agent_hash',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function siteOrigin(): BelongsTo
    {
        return $this->belongsTo(SiteOrigin::class);
    }

    public function pushSubscription(): BelongsTo
    {
        return $this->belongsTo(PushSubscription::class);
    }

    public function legacyPushApp(): BelongsTo
    {
        return $this->belongsTo(LegacyPushApp::class);
    }
}
