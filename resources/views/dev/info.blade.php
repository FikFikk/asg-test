<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Information</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">ℹ️ System Information</h1>
            <p class="text-gray-600">Complete system and application details</p>
        </div>

        <!-- Navigation -->
        <div class="bg-white rounded-lg shadow-lg p-4 mb-6">
            <div class="flex flex-wrap gap-2 justify-center">
                <a href="{{ route('dev.cache') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">🗂️ Cache Management</a>
                <a href="{{ route('dev.info') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">ℹ️ System Info</a>
                <a href="{{ route('dev.routes') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">🛣️ Routes</a>
                <a href="{{ route('dev.env') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">🔧 Environment</a>
                <a href="{{ route('dev.logs') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">📝 Logs</a>
                <a href="/" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">🏠 Home</a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- PHP Information -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4 flex items-center">
                    <span class="mr-2">🐘</span>PHP Information
                </h2>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="font-medium">PHP Version:</span>
                        <span class="text-blue-600">{{ PHP_VERSION }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">PHP SAPI:</span>
                        <span class="text-blue-600">{{ php_sapi_name() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Memory Limit:</span>
                        <span class="text-blue-600">{{ ini_get('memory_limit') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Max Execution Time:</span>
                        <span class="text-blue-600">{{ ini_get('max_execution_time') }}s</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Upload Max Filesize:</span>
                        <span class="text-blue-600">{{ ini_get('upload_max_filesize') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Post Max Size:</span>
                        <span class="text-blue-600">{{ ini_get('post_max_size') }}</span>
                    </div>
                </div>
            </div>

            <!-- Laravel Information -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4 flex items-center">
                    <span class="mr-2">🅻</span>Laravel Information
                </h2>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="font-medium">Laravel Version:</span>
                        <span class="text-red-600">{{ app()->version() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Environment:</span>
                        <span class="text-red-600">{{ app()->environment() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Debug Mode:</span>
                        <span class="text-{{ config('app.debug') ? 'green' : 'red' }}-600">{{ config('app.debug') ? 'ON' : 'OFF' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Application URL:</span>
                        <span class="text-red-600">{{ config('app.url') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Timezone:</span>
                        <span class="text-red-600">{{ config('app.timezone') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Locale:</span>
                        <span class="text-red-600">{{ config('app.locale') }}</span>
                    </div>
                </div>
            </div>

            <!-- Server Information -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4 flex items-center">
                    <span class="mr-2">🖥️</span>Server Information
                </h2>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="font-medium">Server Software:</span>
                        <span class="text-green-600">{{ $_SERVER['SERVER_SOFTWARE'] ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Server Name:</span>
                        <span class="text-green-600">{{ $_SERVER['SERVER_NAME'] ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Server Port:</span>
                        <span class="text-green-600">{{ $_SERVER['SERVER_PORT'] ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Document Root:</span>
                        <span class="text-green-600 text-sm">{{ $_SERVER['DOCUMENT_ROOT'] ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">OS:</span>
                        <span class="text-green-600">{{ PHP_OS }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Host:</span>
                        <span class="text-green-600">{{ gethostname() }}</span>
                    </div>
                </div>
            </div>

            <!-- Database Information -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4 flex items-center">
                    <span class="mr-2">🗄️</span>Database Information
                </h2>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="font-medium">Default Connection:</span>
                        <span class="text-purple-600">{{ config('database.default') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Driver:</span>
                        <span class="text-purple-600">{{ config('database.connections.' . config('database.default') . '.driver') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Host:</span>
                        <span class="text-purple-600">{{ config('database.connections.' . config('database.default') . '.host') ?: 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Port:</span>
                        <span class="text-purple-600">{{ config('database.connections.' . config('database.default') . '.port') ?: 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Database:</span>
                        <span class="text-purple-600">{{ config('database.connections.' . config('database.default') . '.database') ?: 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Cache & Storage -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4 flex items-center">
                    <span class="mr-2">💾</span>Cache & Storage
                </h2>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="font-medium">Cache Driver:</span>
                        <span class="text-orange-600">{{ config('cache.default') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Session Driver:</span>
                        <span class="text-orange-600">{{ config('session.driver') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Queue Driver:</span>
                        <span class="text-orange-600">{{ config('queue.default') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Filesystem Disk:</span>
                        <span class="text-orange-600">{{ config('filesystems.default') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Mail Driver:</span>
                        <span class="text-orange-600">{{ config('mail.default') }}</span>
                    </div>
                </div>
            </div>

            <!-- Loaded Extensions -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4 flex items-center">
                    <span class="mr-2">🔌</span>PHP Extensions
                </h2>
                <div class="grid grid-cols-2 gap-2 max-h-60 overflow-y-auto">
                    @foreach(get_loaded_extensions() as $extension)
                        <div class="bg-gray-100 px-2 py-1 rounded text-sm">{{ $extension }}</div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Paths Information -->
        <div class="bg-white rounded-lg shadow-lg p-6 mt-6">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4 flex items-center">
                <span class="mr-2">📁</span>Application Paths
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <div>
                        <span class="font-medium">Base Path:</span>
                        <p class="text-sm text-gray-600 bg-gray-100 p-2 rounded">{{ base_path() }}</p>
                    </div>
                    <div>
                        <span class="font-medium">App Path:</span>
                        <p class="text-sm text-gray-600 bg-gray-100 p-2 rounded">{{ app_path() }}</p>
                    </div>
                    <div>
                        <span class="font-medium">Config Path:</span>
                        <p class="text-sm text-gray-600 bg-gray-100 p-2 rounded">{{ config_path() }}</p>
                    </div>
                </div>
                <div class="space-y-2">
                    <div>
                        <span class="font-medium">Storage Path:</span>
                        <p class="text-sm text-gray-600 bg-gray-100 p-2 rounded">{{ storage_path() }}</p>
                    </div>
                    <div>
                        <span class="font-medium">Public Path:</span>
                        <p class="text-sm text-gray-600 bg-gray-100 p-2 rounded">{{ public_path() }}</p>
                    </div>
                    <div>
                        <span class="font-medium">Resources Path:</span>
                        <p class="text-sm text-gray-600 bg-gray-100 p-2 rounded">{{ resource_path() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>