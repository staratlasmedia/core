<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SdkToken extends CoreModel
{
    protected $hidden = [
        'token_hash',
        'token_encrypted',
    ];

    protected function casts(): array
    {
        return [
            'abilities' => 'array',
            'allowed_origins' => 'array',
            'expires_at' => 'datetime',
            'last_used_at' => 'datetime',
            'revoked_at' => 'datetime',
            'token_encrypted' => 'encrypted',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function apiClient(): BelongsTo
    {
        return $this->belongsTo(ApiClient::class);
    }
}
