<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailSenderIdentity extends CoreModel
{
    protected $hidden = [
        'aws_access_key_id_encrypted',
        'aws_secret_access_key_encrypted',
    ];

    protected function casts(): array
    {
        return [
            'aws_access_key_id_encrypted' => 'encrypted',
            'aws_secret_access_key_encrypted' => 'encrypted',
            'send_enabled' => 'boolean',
            'test_send_enabled' => 'boolean',
            'last_test_result_json' => 'array',
            'last_tested_at' => 'datetime',
            'metadata_json' => 'array',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
