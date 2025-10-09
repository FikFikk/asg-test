<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstanceController;

Route::get('/', function () {
    return view('welcome');
});

// Instance monitoring routes
Route::get('/instance', [InstanceController::class, 'index'])->name('instance.index');
Route::get('/instance/info', [InstanceController::class, 'getInstanceInfoApi'])->name('instance.info');
Route::get('/instance/cpu', [InstanceController::class, 'getCurrentCpu'])->name('instance.cpu');
Route::post('/instance/stress-cpu', [InstanceController::class, 'stressCpu'])->name('instance.stress-cpu');
