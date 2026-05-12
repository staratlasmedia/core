<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudienceTopicChannelSetting extends CoreModel
{
    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'visible_in_forms' => 'boolean',
            'default_selected' => 'boolean',
            'requires_explicit_consent' => 'boolean',
            'metadata_json' => 'array',
        ];
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(AudienceTopic::class, 'audience_topic_id');
    }
}
