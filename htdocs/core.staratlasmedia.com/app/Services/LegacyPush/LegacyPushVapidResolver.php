<?php

namespace App\Services\LegacyPush;

class LegacyPushVapidResolver
{
    public function __construct(
        private readonly LegacyPushReader $reader,
        private readonly LegacyPushSettingsParser $settingsParser,
    ) {}

    /**
     * @return array{source: string, public_key: string, private_key: string, shared: bool, public_key_hash: string}|null
     */
    public function resolve(int $appid): ?array
    {
        $chromeSettings = $this->settingsParser->parse($this->reader->chromePlatformSettings($appid));
        $shared = (int) ($chromeSettings['shared'] ?? 0) === 1;

        if ($shared) {
            $globalSettings = $this->reader->globalVapidSettings();
            $publicKey = $this->nonEmptyString($globalSettings['vapid_public'] ?? null);
            $privateKey = $this->nonEmptyString($globalSettings['vapid_private'] ?? null);
            $source = 'shared';
        } else {
            $publicKey = $this->nonEmptyString($chromeSettings['vapid_public'] ?? null);
            $privateKey = $this->nonEmptyString($chromeSettings['vapid_private'] ?? null);
            $source = 'app-specific';
        }

        if ($publicKey === null || $privateKey === null) {
            return null;
        }

        return [
            'source' => $source,
            'public_key' => $publicKey,
            'private_key' => $privateKey,
            'shared' => $shared,
            'public_key_hash' => hash('sha256', $publicKey),
        ];
    }

    private function nonEmptyString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
