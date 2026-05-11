<?php

namespace App\Services\LegacyPush;

class LegacyPushSettingsParser
{
    /**
     * @return array<string, mixed>
     */
    public function parse(?string $value): array
    {
        if ($value === null || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        $unserialized = @unserialize($value, ['allowed_classes' => false]);

        return is_array($unserialized) ? $unserialized : [];
    }
}
