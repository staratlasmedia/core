<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllowedOrigin extends CoreModel
{
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
