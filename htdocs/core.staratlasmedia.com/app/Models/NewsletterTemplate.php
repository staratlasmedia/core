<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsletterTemplate extends CoreModel
{
    protected static function booted(): void
    {
        static::creating(function (self $template): void {
            $template->uuid ??= (string) \Illuminate\Support\Str::uuid();
        });
    }

    protected function casts(): array
    {
        return [
            'editor_schema_json' => 'array',
            'design_tokens_json' => 'array',
            'metadata_json' => 'array',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
