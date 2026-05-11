<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginEvent extends CoreModel
{
    public const UPDATED_AT = null;

    protected $hidden = [
        'ip_hash',
        'user_agent_hash',
    ];

    protected function casts(): array
    {
        return [
            'success' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
