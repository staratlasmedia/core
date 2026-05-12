<?php

namespace App\Models;

class AudienceTopicTaxonomyMapping extends CoreModel
{
    protected function casts(): array
    {
        return ['metadata_json' => 'array'];
    }
}
