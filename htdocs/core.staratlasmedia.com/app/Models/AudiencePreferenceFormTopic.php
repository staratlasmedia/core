<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudiencePreferenceFormTopic extends CoreModel
{
    protected function casts(): array
    {
        return [
            'default_selected' => 'boolean',
            'required' => 'boolean',
            'visible' => 'boolean',
            'metadata_json' => 'array',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(AudiencePreferenceForm::class, 'audience_preference_form_id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(AudienceTopic::class, 'audience_topic_id');
    }
}
