<?php

use App\Http\Controllers\Auth\AuthExchangeController;
use App\Http\Controllers\Auth\AuthStartController;
use App\Http\Controllers\Auth\SkeletonProviderController;
use App\Http\Middleware\VerifyBridgeHmac;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('core');
});

Route::prefix('auth')->middleware('throttle:60,1')->group(function (): void {
    Route::get('/start', [AuthStartController::class, 'start']);
    Route::get('/popup', [AuthStartController::class, 'popup']);
    Route::get('/silent-check', [AuthStartController::class, 'silentCheck']);
    Route::post('/logout', [SkeletonProviderController::class, 'logout']);

    Route::post('/exchange-code', [AuthExchangeController::class, 'exchange'])
        ->middleware(VerifyBridgeHmac::class);

    Route::post('/passkey/register/options', [SkeletonProviderController::class, 'passkeyRegisterOptions']);
    Route::post('/passkey/register/verify', [SkeletonProviderController::class, 'passkeyRegisterVerify']);
    Route::post('/passkey/login/options', [SkeletonProviderController::class, 'passkeyLoginOptions']);
    Route::post('/passkey/login/verify', [SkeletonProviderController::class, 'passkeyLoginVerify']);

    Route::post('/magic-link/request', [SkeletonProviderController::class, 'magicLinkRequest']);
    Route::post('/magic-link/verify', [SkeletonProviderController::class, 'magicLinkVerify']);

    Route::post('/password/login', [SkeletonProviderController::class, 'passwordLogin']);
    Route::post('/password/register', [SkeletonProviderController::class, 'passwordRegister']);
    Route::post('/password/forgot', [SkeletonProviderController::class, 'passwordForgot']);
    Route::post('/password/reset', [SkeletonProviderController::class, 'passwordReset']);

    Route::get('/oauth/{provider}/redirect', [SkeletonProviderController::class, 'oauthRedirect']);
    Route::get('/oauth/{provider}/callback', [SkeletonProviderController::class, 'oauthCallback']);
});
