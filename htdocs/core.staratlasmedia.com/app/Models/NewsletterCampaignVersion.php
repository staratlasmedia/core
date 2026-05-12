<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsletterCampaignVersion extends CoreModel
{
    protected function casts(): array
    {
        return [
            'editor_schema_json' => 'array',
            'metadata_json' => 'array',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(NewsletterCampaign::class, 'newsletter_campaign_id');
    }
}
