<?php

namespace App\Services\Bridge;

use Illuminate\Support\Str;

class BridgeSecretFactory
{
    /**
     * @return array{secret: string, fingerprint: string}
     */
    public function make(): array
    {
        $secret = 'sacbs_'.Str::random(64);

        return [
            'secret' => $secret,
            'fingerprint' => $this->fingerprint($secret),
        ];
    }

    public function fingerprint(string $secret): string
    {
        return substr(hash('sha256', $secret), 0, 16);
    }
}
