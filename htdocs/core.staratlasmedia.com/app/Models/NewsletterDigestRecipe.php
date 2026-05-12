<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NewsletterDigestRecipe extends CoreModel
{
    protected static function booted(): void
    {
        static::creating(function (self $recipe): void {
            $recipe->uuid ??= (string) \Illuminate\Support\Str::uuid();
        });
    }

    protected function casts(): array
    {
        return [
            'ai_enabled' => 'boolean',
            'require_editorial_approval' => 'boolean',
            'auto_schedule' => 'boolean',
            'auto_send' => 'boolean',
            'selection_rules_json' => 'array',
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

    public function list(): BelongsTo
    {
        return $this->belongsTo(NewsletterList::class, 'newsletter_list_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(NewsletterTemplate::class, 'template_id');
    }

    public function senderIdentity(): BelongsTo
    {
        return $this->belongsTo(EmailSenderIdentity::class, 'sender_identity_id');
    }

    public function sources(): BelongsToMany
    {
        return $this->belongsToMany(EditorialContentSource::class, 'newsletter_digest_recipe_sources')
            ->withPivot(['enabled', 'sort_order', 'metadata_json'])
            ->withTimestamps();
    }

    public function runs(): HasMany
    {
        return $this->hasMany(NewsletterDigestRun::class);
    }
}
