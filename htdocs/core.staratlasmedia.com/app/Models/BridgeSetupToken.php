<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BridgeSetupToken extends CoreModel
{
    protected $hidden = [
        'token_hash',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
            'revoked_at' => 'datetime',
            'metadata_json' => 'array',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function pushGroup(): BelongsTo
    {
        return $this->belongsTo(PushGroup::class);
    }

    public function siteOrigin(): BelongsTo
    {
        return $this->belongsTo(SiteOrigin::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function consumedByInstallation(): BelongsTo
    {
        return $this->belongsTo(BridgeInstallation::class, 'consumed_by_installation_id');
    }

    public function isClaimable(): bool
    {
        return $this->status === 'active'
            && $this->consumed_at === null
            && $this->revoked_at === null
            && $this->expires_at->isFuture();
    }
}
