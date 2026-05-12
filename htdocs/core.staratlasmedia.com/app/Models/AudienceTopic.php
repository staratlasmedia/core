<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class AudienceTopic extends CoreModel
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'metadata_json' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $topic): void {
            $topic->uuid ??= (string) Str::uuid();
        });
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function pushGroup(): BelongsTo
    {
        return $this->belongsTo(PushGroup::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function channelSettings(): HasMany
    {
        return $this->hasMany(AudienceTopicChannelSetting::class);
    }

    public function mappings(): HasMany
    {
        return $this->hasMany(AudienceTopicMapping::class);
    }
}
