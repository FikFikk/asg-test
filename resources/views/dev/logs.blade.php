<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Logs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .log-entry {
            border-left: 4px solid #e5e7eb;
            transition: all 0.2s;
        }

        .log-entry.emergency {
            border-left-color: #dc2626;
            background-color: #fef2f2;
        }

        .log-entry.alert {
            border-left-color: #dc2626;
            background-color: #fef2f2;
        }

        .log-entry.critical {
            border-left-color: #dc2626;
            background-color: #fef2f2;
        }

        .log-entry.error {
            border-left-color: #dc2626;
            background-color: #fef2f2;
        }

        .log-entry.warning {
            border-left-color: #f59e0b;
            background-color: #fffbeb;
        }

        .log-entry.notice {
            border-left-color: #3b82f6;
            background-color: #eff6ff;
        }

        .log-entry.info {
            border-left-color: #10b981;
            background-color: #f0fdf4;
        }

        .log-entry.debug {
            border-left-color: #6b7280;
            background-color: #f9fafb;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">📝 Application Logs</h1>
            <p class="text-gray-600">View and manage application logs</p>
        </div>

        <!-- Navigation -->
        <div class="bg-white rounded-lg shadow-lg p-4 mb-6">
            <div class="flex flex-wrap gap-2 justify-center">
                <a href="{{ route('dev.cache') }}"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">🗂️ Cache
                    Management</a>
                <a href="{{ route('dev.info') }}"
                    class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">ℹ️ System Info</a>
                <a href="{{ route('dev.routes') }}"
                    class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">🛣️ Routes</a>
                <a href="{{ route('dev.env') }}"
                    class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">🔧 Environment</a>
                <a href="{{ route('dev.logs') }}"
                    class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">📝 Logs</a>
                <a href="/" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">🏠
                    Home</a>
            </div>
        </div>

        <!-- Log Controls -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <h2 class="text-xl font-semibold text-gray-800">Log Controls</h2>
                    <button onclick="refreshLogs()"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm">
                        🔄 Refresh
                    </button>
                    <button onclick="toggleAutoRefresh()" id="autoRefreshBtn"
                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded text-sm">
                        ▶️ Auto Refresh (OFF)
                    </button>
                </div>
                <div class="flex items-center gap-4">
                    <select id="logLevelFilter" onchange="filterLogs()"
                        class="px-3 py-2 border border-gray-300 rounded">
                        <option value="">All Levels</option>
                        <option value="emergency">Emergency</option>
                        <option value="alert">Alert</option>
                        <option value="critical">Critical</option>
                        <option value="error">Error</option>
                        <option value="warning">Warning</option>
                        <option value="notice">Notice</option>
                        <option value="info">Info</option>
                        <option value="debug">Debug</option>
                    </select>
                    <form method="POST" action="{{ route('dev.logs.clear') }}" class="inline">
                        @csrf
                        <button type="submit" onclick="return confirm('Are you sure you want to clear all logs?')"
                            class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm">
                            🗑️ Clear Logs
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <strong>Success!</strong> {{ session('success') }}
            </div>
        @endif

        <!-- Log Viewer -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-800">
                        Laravel Log (Last 100 entries)
                    </h2>
                    <div class="text-sm text-gray-600">
                        <span id="logCount">0</span> entries
                    </div>
                </div>
            </div>

            <div class="max-h-96 overflow-y-auto" id="logContainer">
                @if ($logs)
                    <div class="p-6">
                        <pre class="text-sm font-mono text-gray-800 whitespace-pre-wrap" id="logContent">{{ $logs }}</pre>
                    </div>
                @else
                    <div class="p-6 text-center text-gray-500">
                        <div class="text-4xl mb-4">📄</div>
                        <p class="text-lg font-medium">No logs found</p>
                        <p class="text-sm">The log file is empty or doesn't exist yet.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Log File Information -->
        <div class="bg-white rounded-lg shadow-lg p-6 mt-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">📁 Log File Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="text-center">
                    @php
                        $logFile = storage_path('logs/laravel.log');
                        $fileExists = file_exists($logFile);
                        $fileSize = $fileExists ? filesize($logFile) : 0;
                        $fileSizeFormatted = $fileExists ? number_format($fileSize / 1024, 2) . ' KB' : 'N/A';
                    @endphp
                    <div class="text-2xl mb-2">
                        @if ($fileExists)
                            <span class="text-green-500">✅</span>
                        @else
                            <span class="text-red-500">❌</span>
                        @endif
                    </div>
                    <div class="text-sm">
                        <strong>Log File Status</strong><br>
                        {{ $fileExists ? 'Exists' : 'Missing' }}
                    </div>
                </div>

                <div class="text-center">
                    <div class="text-2xl mb-2">📊</div>
                    <div class="text-sm">
                        <strong>File Size</strong><br>
                        {{ $fileSizeFormatted }}
                    </div>
                </div>

                <div class="text-center">
                    @php
                        $lastModified = $fileExists ? date('Y-m-d H:i:s', filemtime($logFile)) : 'N/A';
                    @endphp
                    <div class="text-2xl mb-2">⏰</div>
                    <div class="text-sm">
                        <strong>Last Modified</strong><br>
                        {{ $lastModified }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Log Level Legend -->
        <div class="bg-white rounded-lg shadow-lg p-6 mt-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">🎨 Log Level Legend</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                <div class="log-entry emergency p-3 rounded">
                    <strong>Emergency</strong><br>
                    <small>System is unusable</small>
                </div>
                <div class="log-entry alert p-3 rounded">
                    <strong>Alert</strong><br>
                    <small>Action must be taken</small>
                </div>
                <div class="log-entry critical p-3 rounded">
                    <strong>Critical</strong><br>
                    <small>Critical conditions</small>
                </div>
                <div class="log-entry error p-3 rounded">
                    <strong>Error</strong><br>
                    <small>Error conditions</small>
                </div>
                <div class="log-entry warning p-3 rounded">
                    <strong>Warning</strong><br>
                    <small>Warning conditions</small>
                </div>
                <div class="log-entry notice p-3 rounded">
                    <strong>Notice</strong><br>
                    <small>Normal but significant</small>
                </div>
                <div class="log-entry info p-3 rounded">
                    <strong>Info</strong><br>
                    <small>Informational messages</small>
                </div>
                <div class="log-entry debug p-3 rounded">
                    <strong>Debug</strong><br>
                    <small>Debug-level messages</small>
                </div>
            </div>
        </div>
    </div>

    <script>
        let autoRefreshInterval;
        let isAutoRefreshOn = false;

        function refreshLogs() {
            window.location.reload();
        }

        function toggleAutoRefresh() {
            const btn = document.getElementById('autoRefreshBtn');

            if (isAutoRefreshOn) {
                clearInterval(autoRefreshInterval);
                btn.textContent = '▶️ Auto Refresh (OFF)';
                btn.className = 'bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded text-sm';
                isAutoRefreshOn = false;
            } else {
                autoRefreshInterval = setInterval(refreshLogs, 10000); // Refresh every 10 seconds
                btn.textContent = '⏸️ Auto Refresh (ON)';
                btn.className = 'bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded text-sm';
                isAutoRefreshOn = true;
            }
        }

        function filterLogs() {
            const filter = document.getElementById('logLevelFilter').value.toLowerCase();
            const logContent = document.getElementById('logContent');

            if (!logContent) return;

            const lines = logContent.textContent.split('\n');
            let filteredLines = lines;

            if (filter) {
                filteredLines = lines.filter(line =>
                    line.toLowerCase().includes('.' + filter + ':') ||
                    line.toLowerCase().includes('local.' + filter + ':')
                );
            }

            logContent.textContent = filteredLines.join('\n');
            document.getElementById('logCount').textContent = filteredLines.length;
        }

        // Initialize log count
        document.addEventListener('DOMContentLoaded', function() {
            const logContent = document.getElementById('logContent');
            if (logContent) {
                const lines = logContent.textContent.split('\n').filter(line => line.trim());
                document.getElementById('logCount').textContent = lines.length;
            }
        });

        // Auto-scroll to bottom of logs
        const logContainer = document.getElementById('logContainer');
        if (logContainer) {
            logContainer.scrollTop = logContainer.scrollHeight;
        }
    </script>
</body>

</html>
