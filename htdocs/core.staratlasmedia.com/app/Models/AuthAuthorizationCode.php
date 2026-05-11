<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthAuthorizationCode extends CoreModel
{
    protected $hidden = [
        'code_hash',
        'state_hash',
        'nonce_hash',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
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
