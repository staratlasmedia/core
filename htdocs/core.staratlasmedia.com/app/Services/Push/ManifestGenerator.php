<?php

namespace App\Services\Push;

use App\Models\PushGroup;

class ManifestGenerator
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(PushGroup $pushGroup): array
    {
        $config = $pushGroup->pwa_config_json ?? [];

        $manifest = [
            'id' => $pushGroup->manifest_id,
            'name' => $pushGroup->manifest_name ?: $pushGroup->name,
            'short_name' => $pushGroup->manifest_short_name ?: $pushGroup->name,
            'scope' => $pushGroup->manifest_scope,
            'start_url' => $pushGroup->manifest_start_url,
        ];

        foreach ([
            'description',
            'categories',
            'background_color',
            'theme_color',
            'display',
            'orientation',
            'lang',
            'dir',
            'icons',
        ] as $key) {
            if (array_key_exists($key, $config)) {
                $manifest[$key] = $config[$key];
            }
        }

        return array_filter($manifest, static fn (mixed $value): bool => $value !== null && $value !== '');
    }

    public function generate(PushGroup $pushGroup): string
    {
        return json_encode(
            $this->toArray($pushGroup),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        ).PHP_EOL;
    }
}
