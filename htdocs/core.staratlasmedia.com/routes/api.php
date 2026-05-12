<?php

use App\Http\Controllers\Bridge\BridgePluginController;
use App\Http\Controllers\Bridge\BridgeSetupController;
use App\Http\Controllers\Comments\BridgeCommentController;
use App\Http\Controllers\Comments\InternalCommentModerationController;
use App\Http\Controllers\Comments\PublicCommentController;
use App\Http\Controllers\Comments\PublicCommentThreadController;
use App\Http\Controllers\Newsletter\NewsletterTrackingController;
use App\Http\Controllers\Newsletter\PublicNewsletterController;
use App\Http\Controllers\Webhooks\AwsSesSnsWebhookController;
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

    Route::get('/comments/threads/resolve', [PublicCommentThreadController::class, 'resolve'])
        ->middleware('throttle:120,1');
    Route::get('/comments', [PublicCommentController::class, 'index'])
        ->middleware('throttle:120,1');

    Route::post('/newsletter/subscribe', [PublicNewsletterController::class, 'subscribe'])
        ->middleware('throttle:30,1');
    Route::post('/newsletter/confirm', [PublicNewsletterController::class, 'confirm'])
        ->middleware('throttle:30,1');
    Route::post('/newsletter/unsubscribe', [PublicNewsletterController::class, 'unsubscribe'])
        ->middleware('throttle:30,1');
    Route::match(['get', 'post'], '/newsletter/preferences', [PublicNewsletterController::class, 'preferences'])
        ->middleware('throttle:60,1');
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
        Route::post('/comments', [BridgeCommentController::class, 'store']);
        Route::post('/comments/{comment}/reactions', [BridgeCommentController::class, 'react']);
        Route::delete('/comments/{comment}/reactions/{reactionType}', [BridgeCommentController::class, 'destroyReaction']);
        Route::post('/comments/{comment}/reports', [BridgeCommentController::class, 'report']);
        Route::post('/newsletter/subscribe', [PublicNewsletterController::class, 'subscribe']);
    });

    Route::get('/plugin/download/{token}', [BridgePluginController::class, 'download'])
        ->name('bridge.plugin.download')
        ->middleware('throttle:60,1');
});

Route::prefix('internal')->middleware(['auth', 'throttle:30,1'])->group(function (): void {
    Route::patch('/comments/{comment}/moderate', [InternalCommentModerationController::class, 'moderate']);
    Route::patch('/comments/threads/{thread}', [InternalCommentModerationController::class, 'updateThread']);
});

Route::post('/webhooks/aws/sns/ses', AwsSesSnsWebhookController::class)
    ->middleware('throttle:120,1');

Route::get('/newsletter/o/{token}.gif', [NewsletterTrackingController::class, 'open'])
    ->middleware('throttle:240,1');
Route::get('/newsletter/c/{token}', [NewsletterTrackingController::class, 'click'])
    ->middleware('throttle:240,1');
