<?php

namespace App\Services\Newsletter\Exceptions;

use RuntimeException;

class NewsletterOperationBlocked extends RuntimeException
{
    public static function forReason(string $reason): self
    {
        return new self($reason);
    }
}
