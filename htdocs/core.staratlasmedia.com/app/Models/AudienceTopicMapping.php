<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudienceTopicMapping extends CoreModel
{
    protected function casts(): array
    {
        return ['metadata_json' => 'array'];
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(AudienceTopic::class, 'audience_topic_id');
    }
}
