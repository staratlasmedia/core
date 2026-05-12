<?php

namespace App\Models;

class EditorialContentSourceTaxonomy extends CoreModel
{
    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'import_terms' => 'boolean',
            'map_to_audience_topics' => 'boolean',
            'metadata_json' => 'array',
        ];
    }
}
