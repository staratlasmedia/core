<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AudiencePreferenceForm extends CoreModel
{
    protected function casts(): array
    {
        return [
            'require_at_least_one_topic' => 'boolean',
            'show_select_all' => 'boolean',
            'metadata_json' => 'array',
        ];
    }

    public function formTopics(): HasMany
    {
        return $this->hasMany(AudiencePreferenceFormTopic::class);
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
