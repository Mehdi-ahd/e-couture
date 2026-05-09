<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\SocialAuthController;
use App\Http\Controllers\Api\Mobile\MobileAuthSyncController;
use App\Http\Controllers\Api\Mobile\MobileWorkspaceController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->name('api.auth.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::post('register', [AuthController::class, 'register'])->name('register');
        Route::post('login', [AuthController::class, 'login'])->name('login');
        Route::get('social/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('social.redirect');
        Route::get('social/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me'])->name('me');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    });
});

Route::prefix('mobile')->name('api.mobile.')->group(function () {
    Route::post('auth/sync', MobileAuthSyncController::class)->name('auth.sync');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('workspace', MobileWorkspaceController::class)->name('workspace');
    });
});
