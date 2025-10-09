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

// Development & Debug Routes (hanya untuk environment local/staging)
Route::group(['middleware' => function ($request, $next) {
    if (!in_array(app()->environment(), ['local', 'staging'])) {
        abort(404);
    }
    return $next($request);
}], function () {
    
    // Cache Management Routes
    Route::prefix('dev')->group(function () {
        
        // Cache Operations
        Route::get('/cache', function () {
            return view('dev.cache');
        })->name('dev.cache');
        
        Route::post('/cache/clear', function () {
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            return back()->with('success', 'Application cache cleared successfully!');
        })->name('dev.cache.clear');
        
        Route::post('/cache/config', function () {
            \Illuminate\Support\Facades\Artisan::call('config:cache');
            return back()->with('success', 'Configuration cached successfully!');
        })->name('dev.cache.config');
        
        Route::post('/cache/route', function () {
            \Illuminate\Support\Facades\Artisan::call('route:cache');
            return back()->with('success', 'Routes cached successfully!');
        })->name('dev.cache.route');
        
        Route::post('/cache/view', function () {
            \Illuminate\Support\Facades\Artisan::call('view:cache');
            return back()->with('success', 'Views cached successfully!');
        })->name('dev.cache.view');
        
        Route::post('/cache/event', function () {
            \Illuminate\Support\Facades\Artisan::call('event:cache');
            return back()->with('success', 'Events cached successfully!');
        })->name('dev.cache.event');
        
        // Clear Operations
        Route::post('/clear/config', function () {
            \Illuminate\Support\Facades\Artisan::call('config:clear');
            return back()->with('success', 'Configuration cache cleared!');
        })->name('dev.clear.config');
        
        Route::post('/clear/route', function () {
            \Illuminate\Support\Facades\Artisan::call('route:clear');
            return back()->with('success', 'Route cache cleared!');
        })->name('dev.clear.route');
        
        Route::post('/clear/view', function () {
            \Illuminate\Support\Facades\Artisan::call('view:clear');
            return back()->with('success', 'View cache cleared!');
        })->name('dev.clear.view');
        
        Route::post('/clear/event', function () {
            \Illuminate\Support\Facades\Artisan::call('event:clear');
            return back()->with('success', 'Event cache cleared!');
        })->name('dev.clear.event');
        
        // Optimization
        Route::post('/optimize', function () {
            \Illuminate\Support\Facades\Artisan::call('optimize');
            return back()->with('success', 'Application optimized successfully!');
        })->name('dev.optimize');
        
        Route::post('/optimize/clear', function () {
            \Illuminate\Support\Facades\Artisan::call('optimize:clear');
            return back()->with('success', 'Optimization cache cleared!');
        })->name('dev.optimize.clear');
        
        // Debug Info
        Route::get('/info', function () {
            return view('dev.info');
        })->name('dev.info');
        
        Route::get('/routes', function () {
            $routes = collect(\Illuminate\Support\Facades\Route::getRoutes())->map(function ($route) {
                return [
                    'uri' => $route->uri(),
                    'methods' => implode('|', $route->methods()),
                    'name' => $route->getName(),
                    'action' => $route->getActionName(),
                    'middleware' => implode(', ', $route->gatherMiddleware()),
                ];
            });
            
            return view('dev.routes', compact('routes'));
        })->name('dev.routes');
        
        // Environment Info
        Route::get('/env', function () {
            $envVars = [
                'APP_NAME' => env('APP_NAME'),
                'APP_ENV' => env('APP_ENV'),
                'APP_DEBUG' => env('APP_DEBUG') ? 'true' : 'false',
                'APP_URL' => env('APP_URL'),
                'DB_CONNECTION' => env('DB_CONNECTION'),
                'CACHE_STORE' => env('CACHE_STORE'),
                'SESSION_DRIVER' => env('SESSION_DRIVER'),
                'QUEUE_CONNECTION' => env('QUEUE_CONNECTION'),
                'INSTANCE_NAME' => env('INSTANCE_NAME'),
            ];
            
            return view('dev.env', compact('envVars'));
        })->name('dev.env');
        
        // Storage Links
        Route::post('/storage/link', function () {
            \Illuminate\Support\Facades\Artisan::call('storage:link');
            return back()->with('success', 'Storage link created successfully!');
        })->name('dev.storage.link');
        
        // Queue Operations
        Route::post('/queue/work', function () {
            \Illuminate\Support\Facades\Artisan::call('queue:work', ['--once' => true]);
            return back()->with('success', 'Queue processed!');
        })->name('dev.queue.work');
        
        Route::post('/queue/restart', function () {
            \Illuminate\Support\Facades\Artisan::call('queue:restart');
            return back()->with('success', 'Queue workers restarted!');
        })->name('dev.queue.restart');
        
        // Migration Operations
        Route::post('/migrate', function () {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            return back()->with('success', 'Migrations executed successfully!');
        })->name('dev.migrate');
        
        Route::post('/migrate/fresh', function () {
            \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);
            return back()->with('success', 'Database refreshed successfully!');
        })->name('dev.migrate.fresh');
        
        Route::post('/migrate/seed', function () {
            \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
            return back()->with('success', 'Database seeded successfully!');
        })->name('dev.migrate.seed');
        
        // Logs
        Route::get('/logs', function () {
            $logFile = storage_path('logs/laravel.log');
            $logs = '';
            
            if (file_exists($logFile)) {
                $logs = file_get_contents($logFile);
                // Get last 100 lines
                $logLines = explode("\n", $logs);
                $logs = implode("\n", array_slice($logLines, -100));
            }
            
            return view('dev.logs', compact('logs'));
        })->name('dev.logs');
        
        Route::post('/logs/clear', function () {
            $logFile = storage_path('logs/laravel.log');
            if (file_exists($logFile)) {
                file_put_contents($logFile, '');
            }
            return back()->with('success', 'Logs cleared successfully!');
        })->name('dev.logs.clear');
    });
});
