<?php

namespace App\Services\Newsletter;

class EmailAddressHasher
{
    public function normalize(string $email): string
    {
        return strtolower(trim($email));
    }

    public function hash(string $email): string
    {
        return hash('sha256', $this->normalize($email));
    }
}
