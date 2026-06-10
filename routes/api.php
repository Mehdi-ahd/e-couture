<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\SocialAuthController;
use App\Http\Controllers\Api\Mobile\MobileAuthSyncController;
use App\Http\Controllers\Api\Mobile\MobileClientController;
use App\Http\Controllers\Api\Mobile\MobileFormeDecoupeController;
use App\Http\Controllers\Api\Mobile\MobileGuidedMeasurementSheetController;
use App\Http\Controllers\Api\Mobile\MobileMateriauController;
use App\Http\Controllers\Api\Mobile\MobileMeasurementSheetController;
use App\Http\Controllers\Api\Mobile\MobileOnboardingController;
use App\Http\Controllers\Api\Mobile\MobileOrderController;
use App\Http\Controllers\Api\Mobile\MobilePatternController;
use App\Http\Controllers\Api\Mobile\MobilePatternPieceController;
use App\Http\Controllers\Api\Mobile\MobilePatternScanController;
use App\Http\Controllers\Api\Mobile\MobilePieceDispositionController;
use App\Http\Controllers\Api\Mobile\MobilePingController;
use App\Http\Controllers\Api\Mobile\MobileRemoveBgAccountController;
use App\Http\Controllers\Api\Mobile\MobileScanController;
use App\Http\Controllers\Api\Mobile\MobileTypeVetementController;
use App\Http\Controllers\Api\Mobile\MobileWorkspaceController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->name('api.auth.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::post('register', [AuthController::class, 'register'])->name('register');
        Route::post('login', [AuthController::class, 'login'])->name('login');
        Route::post('social', [AuthController::class, 'social'])->name('social');
        Route::get('social/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('social.redirect');
        Route::get('social/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me'])->name('me');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    });
});

Route::prefix('mobile')->name('api.mobile.')->group(function () {
    Route::get('ping', MobilePingController::class)->name('ping');
    Route::post('auth/sync', MobileAuthSyncController::class)->name('auth.sync');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('workspace', MobileWorkspaceController::class)->name('workspace');
        Route::post('onboarding/complete', MobileOnboardingController::class)->name('onboarding.complete');
        Route::apiResource('clients', MobileClientController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::apiResource('clients.measurement-sheets', MobileMeasurementSheetController::class)
            ->parameters(['clients' => 'client', 'measurement-sheets' => 'sheet'])
            ->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::post('clients/{client}/measurement-sheets/{sheet}/validate', [MobileMeasurementSheetController::class, 'validateSheet'])
            ->name('clients.measurement-sheets.validate');
        Route::post('clients/{client}/guided-measurement-sheets', [MobileGuidedMeasurementSheetController::class, 'store'])->name('clients.guided-measurement-sheets.store');
        Route::get('clients/{client}/guided-measurement-sheets/{sheet}', [MobileGuidedMeasurementSheetController::class, 'show'])->name('clients.guided-measurement-sheets.show');
        Route::apiResource('clients.orders', MobileOrderController::class)
            ->parameters(['clients' => 'client', 'orders' => 'order'])
            ->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::apiResource('patterns', MobilePatternController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::apiResource('patterns.pieces', MobilePatternPieceController::class)
            ->parameters(['patterns' => 'pattern', 'pieces' => 'piece'])
            ->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::post('scan/pattern', MobilePatternScanController::class)->name('scan.pattern');
        Route::get('scan/pattern/{scan}/cutout', [MobilePatternScanController::class, 'downloadCutout'])->name('scan.pattern.cutout');
        Route::get('remove-bg/account', MobileRemoveBgAccountController::class)->name('remove-bg.account');
        Route::apiResource('pieces.dispositions', MobilePieceDispositionController::class)
            ->parameters(['pieces' => 'piece', 'dispositions' => 'disposition'])
            ->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::apiResource('type-vetements', MobileTypeVetementController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::apiResource('formes-decoupe', MobileFormeDecoupeController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::apiResource('materiaux', MobileMateriauController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::post('scan', MobileScanController::class)->name('scan');
    });
});
