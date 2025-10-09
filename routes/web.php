<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstanceController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index']);

// Instance monitoring routes
Route::get('/instance', [InstanceController::class, 'index'])->name('instance.index');
Route::get('/instance/info', [InstanceController::class, 'getInstanceInfoApi'])->name('instance.info');
Route::get('/instance/cpu', [InstanceController::class, 'getCurrentCpu'])->name('instance.cpu');
Route::post('/instance/stress-cpu', [InstanceController::class, 'stressCpu'])->name('instance.stress-cpu');
Route::get('/instance/debug', [InstanceController::class, 'debugInfo'])->name('instance.debug');

// Emergency cache clear route (untuk production issues)
Route::get('/emergency/clear-cache', [SystemController::class, 'emergencyClearCache'])->name('emergency.clear-cache');

// Development & Debug Routes (hanya untuk environment local/staging)  
Route::middleware(['dev.only'])->prefix('dev')->group(function () {
    // Cache Operations
    Route::get('/cache', [SystemController::class, 'cachePage'])->name('dev.cache');
    Route::post('/cache/clear', [SystemController::class, 'clearCache'])->name('dev.cache.clear');
    Route::post('/cache/config', [SystemController::class, 'cacheConfig'])->name('dev.cache.config');
    Route::post('/cache/route', [SystemController::class, 'cacheRoute'])->name('dev.cache.route');
    Route::post('/cache/view', [SystemController::class, 'cacheView'])->name('dev.cache.view');
    Route::post('/cache/clear-all', [SystemController::class, 'clearAllCache'])->name('dev.cache.clear-all');

    // System Information
    Route::get('/phpinfo', [SystemController::class, 'showPhpInfo'])->name('dev.phpinfo');
    Route::get('/system-info', [SystemController::class, 'showSystemInfo'])->name('dev.system-info');
    Route::get('/env', [SystemController::class, 'showEnvVariables'])->name('dev.env');

    // Debug Tools
    Route::get('/routes', [SystemController::class, 'showRoutes'])->name('dev.routes');
    Route::get('/logs', [SystemController::class, 'viewLogs'])->name('dev.logs');

    // Main Development Dashboard
    Route::get('/', [SystemController::class, 'showDashboard'])->name('dev.dashboard');
});
