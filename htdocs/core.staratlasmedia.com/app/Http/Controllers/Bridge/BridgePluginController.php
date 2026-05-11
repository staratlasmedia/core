<?php

namespace App\Http\Controllers\Bridge;

use App\Http\Controllers\Controller;
use App\Models\BridgeInstallation;
use App\Models\PluginUpdateDownload;
use App\Services\Bridge\BridgePluginUpdateService;
use App\Services\Bridge\PluginDownloadTokenFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BridgePluginController extends Controller
{
    public function updateCheck(Request $request, BridgePluginUpdateService $updates): JsonResponse
    {
        /** @var BridgeInstallation $installation */
        $installation = $request->attributes->get('bridge_installation');
        $channel = (string) $request->query('channel', config('core.bridge.default_update_channel', 'stable'));

        return response()->json($updates->updateMetadata($installation, $channel));
    }

    public function info(Request $request, BridgePluginUpdateService $updates): JsonResponse
    {
        $channel = (string) $request->query('channel', config('core.bridge.default_update_channel', 'stable'));

        return response()->json($updates->pluginInfo($channel));
    }

    public function download(string $token, Request $request, PluginDownloadTokenFactory $tokens): JsonResponse|StreamedResponse
    {
        $download = PluginUpdateDownload::query()
            ->with('pluginRelease')
            ->where('download_token_hash', $tokens->hash($token))
            ->first();

        if (! $download instanceof PluginUpdateDownload || $download->status !== 'issued' || ($download->expires_at !== null && $download->expires_at->isPast())) {
            return response()->json(['message' => 'Download token is invalid or expired.'], 404);
        }

        $release = $download->pluginRelease;

        if ($release->status !== 'published' || $release->zip_storage_path === null || ! Storage::exists($release->zip_storage_path)) {
            $download->forceFill([
                'status' => 'failed',
                'ip_hash' => $request->ip() === null ? null : hash('sha256', $request->ip()),
                'user_agent_hash' => $request->userAgent() === null ? null : hash('sha256', $request->userAgent()),
            ])->save();

            return response()->json(['message' => 'Release package is not available.'], 404);
        }

        $download->forceFill([
            'status' => 'downloaded',
            'downloaded_at' => now(),
            'ip_hash' => $request->ip() === null ? null : hash('sha256', $request->ip()),
            'user_agent_hash' => $request->userAgent() === null ? null : hash('sha256', $request->userAgent()),
        ])->save();

        return Storage::download($release->zip_storage_path, 'star-atlas-core-bridge-'.$release->version.'.zip');
    }
}
