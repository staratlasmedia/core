<?php

namespace App\Models;

class AudiencePreferenceEvent extends CoreModel
{
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return ['metadata_json' => 'array'];
    }
}
