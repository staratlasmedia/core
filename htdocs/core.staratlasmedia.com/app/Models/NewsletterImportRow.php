<?php

namespace App\Models;

class NewsletterImportRow extends CoreModel
{
    protected $hidden = ['email_encrypted'];

    protected function casts(): array
    {
        return [
            'raw_row_json' => 'array',
            'email_encrypted' => 'encrypted',
            'validation_errors_json' => 'array',
            'metadata_json' => 'array',
        ];
    }
}
