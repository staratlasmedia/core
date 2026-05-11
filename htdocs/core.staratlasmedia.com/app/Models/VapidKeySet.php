<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VapidKeySet extends CoreModel
{
    protected $hidden = [
        'private_key_encrypted',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'metadata' => 'array',
            'private_key_encrypted' => 'encrypted',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function legacyPushApp(): BelongsTo
    {
        return $this->belongsTo(LegacyPushApp::class);
    }

    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class);
    }
}
