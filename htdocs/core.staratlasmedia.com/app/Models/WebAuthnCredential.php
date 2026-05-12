<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebAuthnCredential extends CoreModel
{
    protected $hidden = [
        'credential_id_encrypted',
    ];

    protected function casts(): array
    {
        return [
            'credential_id_encrypted' => 'encrypted',
            'transports_json' => 'array',
            'last_used_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
