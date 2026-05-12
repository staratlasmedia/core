<?php

namespace App\Models;

class EditorialContentSourcePostType extends CoreModel
{
    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'include_in_digest' => 'boolean',
            'metadata_json' => 'array',
        ];
    }
}
