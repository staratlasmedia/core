<?php

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
