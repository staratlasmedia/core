<?php

namespace App\Http\Controllers\Bridge;

use App\Actions\Bridge\ClaimBridgeSetupTokenAction;
use App\Http\Controllers\Controller;
use App\Models\BridgeInstallation;
use App\Services\Bridge\BridgeConfigBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BridgeSetupController extends Controller
{
    public function claim(Request $request, ClaimBridgeSetupTokenAction $action): JsonResponse
    {
        $payload = $request->validate([
            'setup_token' => ['required', 'string'],
            'wp_home_url' => ['required', 'url'],
            'wp_site_url' => ['nullable', 'url'],
            'detected_origin' => ['required', 'url'],
            'detected_base_path' => ['required', 'string'],
            'wordpress_version' => ['nullable', 'string', 'max:64'],
            'php_version' => ['nullable', 'string', 'max:64'],
            'plugin_version' => ['nullable', 'string', 'max:64'],
        ]);

        return response()->json($action->execute($payload), 201);
    }

    public function config(Request $request, BridgeConfigBuilder $builder): JsonResponse
    {
        /** @var BridgeInstallation $installation */
        $installation = $request->attributes->get('bridge_installation');
        $installation->forceFill(['last_config_sync_at' => now()])->save();

        return response()->json([
            'config' => $builder->forInstallation($installation->fresh(['site', 'pushGroup', 'siteOrigin'])),
        ]);
    }

    public function heartbeat(Request $request): JsonResponse
    {
        /** @var BridgeInstallation $installation */
        $installation = $request->attributes->get('bridge_installation');
        $data = $request->validate([
            'plugin_version' => ['nullable', 'string', 'max:64'],
            'wordpress_version' => ['nullable', 'string', 'max:64'],
            'php_version' => ['nullable', 'string', 'max:64'],
            'status' => ['nullable', 'string', 'max:64'],
            'metadata' => ['nullable', 'array'],
        ]);

        $installation->forceFill([
            'plugin_version' => $data['plugin_version'] ?? $installation->plugin_version,
            'wordpress_version' => $data['wordpress_version'] ?? $installation->wordpress_version,
            'php_version' => $data['php_version'] ?? $installation->php_version,
            'status' => $data['status'] ?? $installation->status,
            'metadata_json' => $data['metadata'] ?? $installation->metadata_json,
            'last_seen_at' => now(),
        ])->save();

        return response()->json([
            'status' => 'ok',
            'last_seen_at' => $installation->last_seen_at?->toISOString(),
        ]);
    }

    public function events(Request $request): JsonResponse
    {
        /** @var BridgeInstallation $installation */
        $installation = $request->attributes->get('bridge_installation');

        return response()->json([
            'status' => 'accepted',
            'bridge_installation_id' => $installation->uuid,
        ], 202);
    }
}
