<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BridgeConfigVersion extends CoreModel
{
    protected function casts(): array
    {
        return [
            'config_json' => 'array',
            'active' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function bridgeInstallation(): BelongsTo
    {
        return $this->belongsTo(BridgeInstallation::class);
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
