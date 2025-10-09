<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Environment Variables</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">🔧 Environment Variables</h1>
            <p class="text-gray-600">Application configuration and environment settings</p>
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

        <!-- Warning Notice -->
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-6">
            <div class="flex items-center">
                <span class="text-xl mr-2">⚠️</span>
                <div>
                    <strong>Security Notice:</strong> Sensitive data like passwords and API keys are hidden for security reasons.
                    This page only shows safe configuration values.
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Application Configuration -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4 flex items-center">
                    <span class="mr-2">🅰️</span>Application Configuration
                </h2>
                <div class="space-y-3">
                    @foreach($envVars as $key => $value)
                        @if(in_array($key, ['APP_NAME', 'APP_ENV', 'APP_DEBUG', 'APP_URL']))
                            <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                                <span class="font-medium text-gray-700">{{ $key }}:</span>
                                <span class="text-blue-600 font-mono bg-blue-50 px-2 py-1 rounded">{{ $value ?: 'Not Set' }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Database Configuration -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4 flex items-center">
                    <span class="mr-2">🗄️</span>Database Configuration
                </h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                        <span class="font-medium text-gray-700">DB_CONNECTION:</span>
                        <span class="text-purple-600 font-mono bg-purple-50 px-2 py-1 rounded">{{ $envVars['DB_CONNECTION'] ?: 'Not Set' }}</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                        <span class="font-medium text-gray-700">DB_HOST:</span>
                        <span class="text-purple-600 font-mono bg-purple-50 px-2 py-1 rounded">{{ env('DB_HOST') ?: 'Not Set' }}</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                        <span class="font-medium text-gray-700">DB_PORT:</span>
                        <span class="text-purple-600 font-mono bg-purple-50 px-2 py-1 rounded">{{ env('DB_PORT') ?: 'Not Set' }}</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                        <span class="font-medium text-gray-700">DB_DATABASE:</span>
                        <span class="text-purple-600 font-mono bg-purple-50 px-2 py-1 rounded">{{ env('DB_DATABASE') ?: 'Not Set' }}</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                        <span class="font-medium text-gray-700">DB_USERNAME:</span>
                        <span class="text-gray-500 font-mono bg-gray-50 px-2 py-1 rounded">{{ env('DB_USERNAME') ? '***HIDDEN***' : 'Not Set' }}</span>
                    </div>
                </div>
            </div>

            <!-- Cache & Session Configuration -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4 flex items-center">
                    <span class="mr-2">💾</span>Cache & Session
                </h2>
                <div class="space-y-3">
                    @foreach($envVars as $key => $value)
                        @if(in_array($key, ['CACHE_STORE', 'SESSION_DRIVER', 'QUEUE_CONNECTION']))
                            <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                                <span class="font-medium text-gray-700">{{ $key }}:</span>
                                <span class="text-orange-600 font-mono bg-orange-50 px-2 py-1 rounded">{{ $value ?: 'Not Set' }}</span>
                            </div>
                        @endif
                    @endforeach
                    <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                        <span class="font-medium text-gray-700">FILESYSTEM_DISK:</span>
                        <span class="text-orange-600 font-mono bg-orange-50 px-2 py-1 rounded">{{ env('FILESYSTEM_DISK') ?: 'Not Set' }}</span>
                    </div>
                </div>
            </div>

            <!-- Instance Configuration -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4 flex items-center">
                    <span class="mr-2">🖥️</span>Instance Configuration
                </h2>
                <div class="space-y-3">
                    @if($envVars['INSTANCE_NAME'])
                        <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                            <span class="font-medium text-gray-700">INSTANCE_NAME:</span>
                            <span class="text-green-600 font-mono bg-green-50 px-2 py-1 rounded">{{ $envVars['INSTANCE_NAME'] }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                        <span class="font-medium text-gray-700">AWS_INSTANCE_ID:</span>
                        <span class="text-green-600 font-mono bg-green-50 px-2 py-1 rounded">{{ env('AWS_INSTANCE_ID') ?: 'Not Set' }}</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                        <span class="font-medium text-gray-700">AWS_AVAILABILITY_ZONE:</span>
                        <span class="text-green-600 font-mono bg-green-50 px-2 py-1 rounded">{{ env('AWS_AVAILABILITY_ZONE') ?: 'Not Set' }}</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                        <span class="font-medium text-gray-700">AWS_INSTANCE_TYPE:</span>
                        <span class="text-green-600 font-mono bg-green-50 px-2 py-1 rounded">{{ env('AWS_INSTANCE_TYPE') ?: 'Not Set' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Configuration -->
        <div class="bg-white rounded-lg shadow-lg p-6 mt-6">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4 flex items-center">
                <span class="mr-2">⚙️</span>Additional Configuration
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="space-y-2">
                    <h3 class="font-semibold text-gray-700">Mail Configuration</h3>
                    <div class="text-sm space-y-1">
                        <div class="flex justify-between">
                            <span>Driver:</span>
                            <span class="font-mono bg-gray-100 px-2 py-1 rounded">{{ env('MAIL_MAILER') ?: 'Not Set' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Host:</span>
                            <span class="font-mono bg-gray-100 px-2 py-1 rounded">{{ env('MAIL_HOST') ?: 'Not Set' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Port:</span>
                            <span class="font-mono bg-gray-100 px-2 py-1 rounded">{{ env('MAIL_PORT') ?: 'Not Set' }}</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <h3 class="font-semibold text-gray-700">Logging</h3>
                    <div class="text-sm space-y-1">
                        <div class="flex justify-between">
                            <span>Channel:</span>
                            <span class="font-mono bg-gray-100 px-2 py-1 rounded">{{ env('LOG_CHANNEL') ?: 'Not Set' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Level:</span>
                            <span class="font-mono bg-gray-100 px-2 py-1 rounded">{{ env('LOG_LEVEL') ?: 'Not Set' }}</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <h3 class="font-semibold text-gray-700">Broadcasting</h3>
                    <div class="text-sm space-y-1">
                        <div class="flex justify-between">
                            <span>Driver:</span>
                            <span class="font-mono bg-gray-100 px-2 py-1 rounded">{{ env('BROADCAST_DRIVER') ?: 'Not Set' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Connection:</span>
                            <span class="font-mono bg-gray-100 px-2 py-1 rounded">{{ env('BROADCAST_CONNECTION') ?: 'Not Set' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Environment File Status -->
        <div class="bg-white rounded-lg shadow-lg p-6 mt-6">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4 flex items-center">
                <span class="mr-2">📄</span>Environment File Status
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="text-center">
                    <div class="text-2xl mb-2">
                        @if(file_exists(base_path('.env')))
                            <span class="text-green-500">✅</span>
                        @else
                            <span class="text-red-500">❌</span>
                        @endif
                    </div>
                    <div class="text-sm">
                        <strong>.env File</strong><br>
                        {{ file_exists(base_path('.env')) ? 'Exists' : 'Missing' }}
                    </div>
                </div>

                <div class="text-center">
                    <div class="text-2xl mb-2">
                        @if(file_exists(base_path('.env.example')))
                            <span class="text-green-500">✅</span>
                        @else
                            <span class="text-yellow-500">⚠️</span>
                        @endif
                    </div>
                    <div class="text-sm">
                        <strong>.env.example File</strong><br>
                        {{ file_exists(base_path('.env.example')) ? 'Exists' : 'Missing' }}
                    </div>
                </div>

                <div class="text-center">
                    <div class="text-2xl mb-2">
                        @if(env('APP_KEY'))
                            <span class="text-green-500">✅</span>
                        @else
                            <span class="text-red-500">❌</span>
                        @endif
                    </div>
                    <div class="text-sm">
                        <strong>APP_KEY</strong><br>
                        {{ env('APP_KEY') ? 'Set' : 'Missing' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>