<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel Development Tools</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .btn-primary {
            @apply bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition duration-300;
        }

        .btn-success {
            @apply bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded transition duration-300;
        }

        .btn-warning {
            @apply bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded transition duration-300;
        }

        .btn-danger {
            @apply bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded transition duration-300;
        }

        .btn-secondary {
            @apply bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded transition duration-300;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">🛠️ Laravel Development Tools</h1>
            <p class="text-gray-600">Cache Management & Optimization Tools</p>
            <div class="mt-4">
                <span
                    class="inline-block bg-{{ app()->environment() === 'local' ? 'green' : 'yellow' }}-100 text-{{ app()->environment() === 'local' ? 'green' : 'yellow' }}-800 text-sm font-medium px-3 py-1 rounded-full">
                    Environment: {{ strtoupper(app()->environment()) }}
                </span>
            </div>
        </div>

        <!-- Navigation -->
        <div class="bg-white rounded-lg shadow-lg p-4 mb-6">
            <div class="flex flex-wrap gap-2 justify-center">
                <a href="{{ route('dev.cache') }}" class="btn-primary">🗂️ Cache Management</a>
                <a href="{{ route('dev.info') }}" class="btn-secondary">ℹ️ System Info</a>
                <a href="{{ route('dev.routes') }}" class="btn-secondary">🛣️ Routes</a>
                <a href="{{ route('dev.env') }}" class="btn-secondary">🔧 Environment</a>
                <a href="{{ route('dev.logs') }}" class="btn-secondary">📝 Logs</a>
                <a href="/" class="btn-secondary">🏠 Home</a>
                <a href="/instance" class="btn-secondary">📊 Instance Monitor</a>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <strong>Success!</strong> {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <strong>Error!</strong> {{ session('error') }}
            </div>
        @endif

        <!-- Cache Management Section -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4 flex items-center">
                <span class="mr-2">🗂️</span>Cache Management
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Cache Operations -->
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-blue-800 mb-3">📦 Cache Operations</h3>
                    <div class="space-y-2">
                        <form method="POST" action="{{ route('dev.cache.clear') }}" class="inline">
                            @csrf
                            <button type="submit" class="w-full btn-warning text-sm">Clear Application Cache</button>
                        </form>
                        <form method="POST" action="{{ route('dev.optimize.clear') }}" class="inline">
                            @csrf
                            <button type="submit" class="w-full btn-warning text-sm">Clear All Optimizations</button>
                        </form>
                    </div>
                </div>

                <!-- Config Cache -->
                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-green-800 mb-3">⚙️ Configuration Cache</h3>
                    <div class="space-y-2">
                        <form method="POST" action="{{ route('dev.cache.config') }}" class="inline">
                            @csrf
                            <button type="submit" class="w-full btn-success text-sm">Cache Config</button>
                        </form>
                        <form method="POST" action="{{ route('dev.clear.config') }}" class="inline">
                            @csrf
                            <button type="submit" class="w-full btn-danger text-sm">Clear Config Cache</button>
                        </form>
                    </div>
                </div>

                <!-- Route Cache -->
                <div class="bg-purple-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-purple-800 mb-3">🛣️ Route Cache</h3>
                    <div class="space-y-2">
                        <form method="POST" action="{{ route('dev.cache.route') }}" class="inline">
                            @csrf
                            <button type="submit" class="w-full btn-success text-sm">Cache Routes</button>
                        </form>
                        <form method="POST" action="{{ route('dev.clear.route') }}" class="inline">
                            @csrf
                            <button type="submit" class="w-full btn-danger text-sm">Clear Route Cache</button>
                        </form>
                    </div>
                </div>

                <!-- View Cache -->
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-yellow-800 mb-3">👁️ View Cache</h3>
                    <div class="space-y-2">
                        <form method="POST" action="{{ route('dev.cache.view') }}" class="inline">
                            @csrf
                            <button type="submit" class="w-full btn-success text-sm">Cache Views</button>
                        </form>
                        <form method="POST" action="{{ route('dev.clear.view') }}" class="inline">
                            @csrf
                            <button type="submit" class="w-full btn-danger text-sm">Clear View Cache</button>
                        </form>
                    </div>
                </div>

                <!-- Event Cache -->
                <div class="bg-indigo-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-indigo-800 mb-3">🎉 Event Cache</h3>
                    <div class="space-y-2">
                        <form method="POST" action="{{ route('dev.cache.event') }}" class="inline">
                            @csrf
                            <button type="submit" class="w-full btn-success text-sm">Cache Events</button>
                        </form>
                        <form method="POST" action="{{ route('dev.clear.event') }}" class="inline">
                            @csrf
                            <button type="submit" class="w-full btn-danger text-sm">Clear Event Cache</button>
                        </form>
                    </div>
                </div>

                <!-- Optimization -->
                <div class="bg-red-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-red-800 mb-3">🚀 Optimization</h3>
                    <div class="space-y-2">
                        <form method="POST" action="{{ route('dev.optimize') }}" class="inline">
                            @csrf
                            <button type="submit" class="w-full btn-primary text-sm">Optimize Application</button>
                        </form>
                        <p class="text-xs text-red-600 mt-1">Caches config, routes, views & events</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Database & Storage Section -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4 flex items-center">
                <span class="mr-2">🗄️</span>Database & Storage
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Migrations -->
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-blue-800 mb-3">📋 Migrations</h3>
                    <div class="space-y-2">
                        <form method="POST" action="{{ route('dev.migrate') }}" class="inline">
                            @csrf
                            <button type="submit" class="w-full btn-primary text-sm"
                                onclick="return confirm('Run migrations?')">Run Migrations</button>
                        </form>
                        <form method="POST" action="{{ route('dev.migrate.fresh') }}" class="inline">
                            @csrf
                            <button type="submit" class="w-full btn-danger text-sm"
                                onclick="return confirm('This will drop all tables! Continue?')">Fresh Migrate</button>
                        </form>
                        <form method="POST" action="{{ route('dev.migrate.seed') }}" class="inline">
                            @csrf
                            <button type="submit" class="w-full btn-success text-sm">Seed Database</button>
                        </form>
                    </div>
                </div>

                <!-- Storage -->
                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-green-800 mb-3">💾 Storage</h3>
                    <div class="space-y-2">
                        <form method="POST" action="{{ route('dev.storage.link') }}" class="inline">
                            @csrf
                            <button type="submit" class="w-full btn-success text-sm">Create Storage Link</button>
                        </form>
                    </div>
                </div>

                <!-- Queue -->
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-yellow-800 mb-3">⏳ Queue</h3>
                    <div class="space-y-2">
                        <form method="POST" action="{{ route('dev.queue.work') }}" class="inline">
                            @csrf
                            <button type="submit" class="w-full btn-primary text-sm">Process Queue (Once)</button>
                        </form>
                        <form method="POST" action="{{ route('dev.queue.restart') }}" class="inline">
                            @csrf
                            <button type="submit" class="w-full btn-warning text-sm">Restart Queue Workers</button>
                        </form>
                    </div>
                </div>

                <!-- Logs -->
                <div class="bg-red-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-red-800 mb-3">📝 Logs</h3>
                    <div class="space-y-2">
                        <a href="{{ route('dev.logs') }}" class="block w-full btn-secondary text-sm text-center">View
                            Logs</a>
                        <form method="POST" action="{{ route('dev.logs.clear') }}" class="inline">
                            @csrf
                            <button type="submit" class="w-full btn-danger text-sm"
                                onclick="return confirm('Clear all logs?')">Clear Logs</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4 flex items-center">
                <span class="mr-2">⚡</span>Quick Actions
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Development Setup -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-gray-800 mb-3">🛠️ Development Setup</h3>
                    <div class="space-y-2 text-sm">
                        <button
                            onclick="runMultipleCommands(['cache:clear', 'config:clear', 'route:clear', 'view:clear'])"
                            class="w-full btn-warning text-sm">Clear All Caches</button>
                        <button
                            onclick="runMultipleCommands(['config:cache', 'route:cache', 'view:cache', 'optimize'])"
                            class="w-full btn-primary text-sm">Optimize for Production</button>
                    </div>
                </div>

                <!-- System Info -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-gray-800 mb-3">ℹ️ System Status</h3>
                    <div class="text-sm space-y-1">
                        <p><strong>PHP:</strong> {{ PHP_VERSION }}</p>
                        <p><strong>Laravel:</strong> {{ app()->version() }}</p>
                        <p><strong>Environment:</strong> {{ app()->environment() }}</p>
                        <p><strong>Debug:</strong> {{ config('app.debug') ? 'ON' : 'OFF' }}</p>
                    </div>
                </div>

                <!-- Cache Status -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-gray-800 mb-3">💾 Cache Status</h3>
                    <div class="text-sm space-y-1">
                        <p><strong>Config:</strong> <span
                                class="text-{{ file_exists(base_path('bootstrap/cache/config.php')) ? 'green' : 'red' }}-600">{{ file_exists(base_path('bootstrap/cache/config.php')) ? 'Cached' : 'Not Cached' }}</span>
                        </p>
                        <p><strong>Routes:</strong> <span
                                class="text-{{ file_exists(base_path('bootstrap/cache/routes-v7.php')) ? 'green' : 'red' }}-600">{{ file_exists(base_path('bootstrap/cache/routes-v7.php')) ? 'Cached' : 'Not Cached' }}</span>
                        </p>
                        <p><strong>Views:</strong> <span
                                class="text-{{ count(glob(storage_path('framework/views/*'))) > 0 ? 'green' : 'red' }}-600">{{ count(glob(storage_path('framework/views/*'))) > 0 ? 'Cached' : 'Not Cached' }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function runMultipleCommands(commands) {
            if (confirm(`Run the following commands:\n${commands.join('\n')}`)) {
                // For now, redirect to individual endpoints
                // In a real implementation, you'd create an endpoint that runs multiple commands
                alert('Feature coming soon! Use individual buttons for now.');
            }
        }

        // Auto-hide success/error messages after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.bg-green-100, .bg-red-100');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>

</html>
