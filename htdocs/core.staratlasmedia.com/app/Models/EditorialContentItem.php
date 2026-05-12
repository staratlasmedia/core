<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EditorialContentItem extends CoreModel
{
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'modified_at' => 'datetime',
            'wp_terms_json' => 'array',
            'raw_payload_json' => 'array',
            'metadata_json' => 'array',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(EditorialContentSource::class, 'content_source_id');
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
