<?php

use App\Http\Controllers\Bridge\BridgePluginController;
use App\Http\Controllers\Bridge\BridgeSetupController;
use App\Http\Middleware\VerifyBridgeHmac;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')->prefix('v1')->group(function (): void {
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'service' => 'star-atlas-core',
        ]);
    });

    Route::get('/sites/{siteCode}/bootstrap', function (string $siteCode) {
        $site = config("core.sites.{$siteCode}");

        abort_if($site === null, 404);

        return response()->json([
            'site_code' => $siteCode,
            'origin' => $site['origin'],
            'language' => $site['language'],
            'push_group' => $site['push_group'],
            'manifest_id' => $site['manifest_id'],
            'service_worker_url' => $site['service_worker_url'],
            'service_worker_scope' => $site['service_worker_scope'],
        ]);
    });
});

Route::prefix('bridge')->group(function (): void {
    Route::post('/setup/claim', [BridgeSetupController::class, 'claim'])
        ->middleware('throttle:10,1');

    Route::middleware([VerifyBridgeHmac::class, 'throttle:120,1'])->group(function (): void {
        Route::get('/config', [BridgeSetupController::class, 'config']);
        Route::post('/heartbeat', [BridgeSetupController::class, 'heartbeat']);
        Route::post('/events', [BridgeSetupController::class, 'events']);
        Route::get('/plugin/update-check', [BridgePluginController::class, 'updateCheck']);
        Route::get('/plugin/info', [BridgePluginController::class, 'info']);
    });

    Route::get('/plugin/download/{token}', [BridgePluginController::class, 'download'])
        ->name('bridge.plugin.download')
        ->middleware('throttle:60,1');
});
