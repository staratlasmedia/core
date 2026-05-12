<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EditorialContentSource extends CoreModel
{
    protected $hidden = ['auth_config_encrypted'];

    protected function casts(): array
    {
        return [
            'auth_config_encrypted' => 'encrypted',
            'polling_enabled' => 'boolean',
            'last_polled_at' => 'datetime',
            'last_successful_poll_at' => 'datetime',
            'last_error_at' => 'datetime',
            'metadata_json' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $source): void {
            $source->uuid ??= (string) Str::uuid();
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(EditorialContentItem::class, 'content_source_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function pushGroup(): BelongsTo
    {
        return $this->belongsTo(PushGroup::class);
    }
}
