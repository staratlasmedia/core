<?php

namespace App\Models;

class EditorialContentTaxonomyTerm extends CoreModel
{
    protected function casts(): array
    {
        return ['metadata_json' => 'array'];
    }
}
