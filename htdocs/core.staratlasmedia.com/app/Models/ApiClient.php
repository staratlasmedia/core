<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApiClient extends CoreModel
{
    protected $hidden = [
        'secret_hash',
        'secret_encrypted',
    ];

    protected function casts(): array
    {
        return [
            'allowed_origins' => 'array',
            'permissions' => 'array',
            'secret_encrypted' => 'encrypted',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function sdkTokens(): HasMany
    {
        return $this->hasMany(SdkToken::class);
    }
}
