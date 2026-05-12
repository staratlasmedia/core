<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class NewsletterCampaign extends CoreModel
{
    protected static function booted(): void
    {
        static::creating(function (self $campaign): void {
            $campaign->uuid ??= (string) Str::uuid();
        });
    }

    protected function casts(): array
    {
        return [
            'editor_schema_json' => 'array',
            'segment_json' => 'array',
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'approved_at' => 'datetime',
            'metadata_json' => 'array',
        ];
    }

    public function list(): BelongsTo
    {
        return $this->belongsTo(NewsletterList::class, 'newsletter_list_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(NewsletterTemplate::class, 'template_id');
    }

    public function fromIdentity(): BelongsTo
    {
        return $this->belongsTo(EmailSenderIdentity::class, 'from_identity_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(NewsletterCampaignVersion::class);
    }

    public function deliveryLogs(): HasMany
    {
        return $this->hasMany(NewsletterDeliveryLog::class);
    }
}
