<?php

namespace App\Models;

class NewsletterPreview extends CoreModel
{
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return ['preview_context_json' => 'array'];
    }
}
