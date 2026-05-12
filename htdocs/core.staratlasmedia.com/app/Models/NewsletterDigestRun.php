<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsletterDigestRun extends CoreModel
{
    protected function casts(): array
    {
        return [
            'run_date' => 'date',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'metadata_json' => 'array',
        ];
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(NewsletterDigestRecipe::class, 'newsletter_digest_recipe_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(NewsletterCampaign::class, 'newsletter_campaign_id');
    }
}
