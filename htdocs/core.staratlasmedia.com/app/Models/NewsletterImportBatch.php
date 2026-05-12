<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class NewsletterImportBatch extends CoreModel
{
    protected function casts(): array
    {
        return [
            'mapping_json' => 'array',
            'options_json' => 'array',
            'dry_run_report_json' => 'array',
            'commit_report_json' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'dry_run_completed_at' => 'datetime',
            'committed_at' => 'datetime',
            'metadata_json' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $batch): void {
            $batch->uuid ??= (string) Str::uuid();
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

    public function list(): BelongsTo
    {
        return $this->belongsTo(NewsletterList::class, 'newsletter_list_id');
    }

    public function rows(): HasMany
    {
        return $this->hasMany(NewsletterImportRow::class);
    }

    public function committedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'committed_by');
    }
}
