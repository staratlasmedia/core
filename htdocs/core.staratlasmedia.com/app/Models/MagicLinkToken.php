<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MagicLinkToken extends CoreModel
{
    protected $hidden = [
        'token_hash',
        'ip_hash',
        'user_agent_hash',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
            'metadata_json' => 'array',
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

    public function bridgeInstallation(): BelongsTo
    {
        return $this->belongsTo(BridgeInstallation::class);
    }
}
