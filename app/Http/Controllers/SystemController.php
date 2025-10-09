<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SystemController extends Controller
{
    public function emergencyClearCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            Artisan::call('clear-compiled');

            // Clear file-based caches manually
            $files = glob(storage_path('framework/cache/data/*'));
            foreach ($files as $file) {
                if (is_file($file)) unlink($file);
            }

            $files = glob(storage_path('framework/sessions/*'));
            foreach ($files as $file) {
                if (is_file($file)) unlink($file);
            }

            $files = glob(storage_path('framework/views/*'));
            foreach ($files as $file) {
                if (is_file($file)) unlink($file);
            }

            $files = glob(base_path('bootstrap/cache/*.php'));
            foreach ($files as $file) {
                if (is_file($file) && basename($file) !== '.gitignore') unlink($file);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'All caches cleared successfully! Closure::__set_state() error should be resolved.',
                'timestamp' => now()->toDateTimeString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error clearing cache: ' . $e->getMessage(),
                'timestamp' => now()->toDateTimeString()
            ], 500);
        }
    }

    public function cachePage()
    {
        return view('dev.cache');
    }

    public function clearCache()
    {
        Artisan::call('cache:clear');
        return back()->with('success', 'Application cache cleared successfully!');
    }

    public function cacheConfig()
    {
        Artisan::call('config:cache');
        return back()->with('success', 'Configuration cached successfully!');
    }

    public function cacheRoute()
    {
        Artisan::call('route:cache');
        return back()->with('success', 'Routes cached successfully!');
    }

    public function cacheView()
    {
        Artisan::call('view:cache');
        return back()->with('success', 'Views cached successfully!');
    }

    public function clearAllCache()
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        return back()->with('success', 'All caches cleared successfully!');
    }

    public function showPhpInfo()
    {
        return view('dev.phpinfo');
    }

    public function showSystemInfo()
    {
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'timezone' => config('app.timezone'),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
        ];

        return view('dev.system-info', compact('systemInfo'));
    }

    public function showEnvVariables()
    {
        $envVars = [];
        foreach ($_ENV as $key => $value) {
            if (strpos($key, 'PASSWORD') === false && strpos($key, 'SECRET') === false && strpos($key, 'KEY') === false) {
                $envVars[$key] = $value;
            } else {
                $envVars[$key] = '***HIDDEN***';
            }
        }

        return view('dev.env-variables', compact('envVars'));
    }

    public function showRoutes()
    {
        Artisan::call('route:list');
        $routeList = Artisan::output();

        return view('dev.routes', compact('routeList'));
    }

    public function viewLogs()
    {
        $logPath = storage_path('logs/laravel.log');
        $logs = [];

        if (file_exists($logPath)) {
            $content = file_get_contents($logPath);
            $logs = array_reverse(explode("\n", $content));
            $logs = array_slice($logs, 0, 100); // Last 100 lines
        }

        return view('dev.logs', compact('logs'));
    }

    public function showDashboard()
    {
        return view('dev.dashboard');
    }
}
