<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AWS Instance Monitor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .pulse-animation {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .cpu-meter {
            background: conic-gradient(from 0deg,
                    #100909 0deg {{ $cpuUsage * 3.6 }}deg,
                    #e5e7eb {{ $cpuUsage * 3.6 }}deg 360deg);
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">AWS Instance Monitor</h1>
            <p class="text-gray-600">Real-time monitoring untuk Load Balancer & Auto Scaling Group</p>
        </div>

        <!-- Instance Info Card -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-semibold text-gray-800 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Informasi Instance
                </h2>

                <!-- Debug Button -->
                <button onclick="openDebugModal()"
                    class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-lg text-sm font-semibold transition-colors duration-200 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                        </path>
                    </svg>
                    Debug Info
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-blue-800">Instance Name</h3>
                    <p class="text-blue-600 text-lg font-mono">{{ $instanceInfo['instance_name'] }}</p>
                    <p class="text-xs text-blue-500 mt-1">Auto-generated or custom name</p>
                </div>

                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-green-800">Instance ID</h3>
                    <p class="text-green-600 text-sm font-mono">{{ $instanceInfo['instance_id'] }}</p>
                    <p class="text-xs text-green-500 mt-1">Unique AWS identifier</p>
                </div>

                <div class="bg-purple-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-purple-800">Instance Type</h3>
                    <p class="text-purple-600 text-lg">{{ $instanceInfo['instance_type'] }}</p>
                    <p class="text-xs text-purple-500 mt-1">Hardware specification</p>
                </div>

                <div class="bg-orange-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-orange-800">Availability Zone</h3>
                    <p class="text-orange-600 text-lg">{{ $instanceInfo['availability_zone'] }}</p>
                    <p class="text-xs text-orange-500 mt-1">Region: {{ $instanceInfo['region'] ?? 'N/A' }}</p>
                </div>

                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-gray-800">Private IP</h3>
                    <p class="text-gray-600 text-lg font-mono">{{ $instanceInfo['local_ip'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">Internal network address</p>
                </div>

                <div class="bg-indigo-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-indigo-800">Public IP</h3>
                    <p class="text-indigo-600 text-lg font-mono">{{ $instanceInfo['public_ip'] }}</p>
                    <p class="text-xs text-indigo-500 mt-1">Internet-facing address</p>
                </div>

                <div class="bg-teal-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-teal-800">Hostname</h3>
                    <p class="text-teal-600 text-lg font-mono">{{ $instanceInfo['hostname'] }}</p>
                    <p class="text-xs text-teal-500 mt-1">System hostname</p>
                </div>

                <div class="bg-yellow-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-yellow-800">Environment</h3>
                    <p class="text-yellow-600 text-lg">{{ app()->environment() }}</p>
                    <p class="text-xs text-yellow-500 mt-1">Laravel environment</p>
                </div>

                <div class="bg-pink-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-pink-800">Server Time</h3>
                    <p class="text-pink-600 text-lg" id="server-time">{{ now()->format('H:i:s') }}</p>
                    <p class="text-xs text-pink-500 mt-1">{{ now()->format('Y-m-d') }}</p>
                </div>
            </div>
        </div>

        <!-- CPU Monitoring Card -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                CPU Usage Monitor
            </h2>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- CPU Meter -->
                <div class="text-center">
                    <div class="relative inline-block">
                        <div class="w-32 h-32 rounded-full cpu-meter flex items-center justify-center">
                            <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center">
                                <div class="text-center">
                                    <div id="cpu-percentage" class="text-2xl font-bold text-gray-800">
                                        {{ $cpuUsage }}%</div>
                                    <div class="text-xs text-gray-500">CPU</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="mt-2 text-sm text-gray-600">Current CPU Usage</p>
                </div>

                <!-- CPU Chart -->
                <div>
                    <canvas id="cpuChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- CPU Stress Test Card -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                CPU Stress Test
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Duration (seconds)</label>
                    <input type="number" id="duration" value="10" min="1" max="300"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Intensity (%)</label>
                    <input type="range" id="intensity" value="50" min="10" max="90"
                        class="w-full">
                    <div class="text-center text-sm text-gray-600">
                        <span id="intensity-value">50</span>%
                    </div>
                </div>

                <div class="flex items-end">
                    <button id="stress-test-btn"
                        class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition duration-300">
                        Start Stress Test
                    </button>
                </div>
            </div>

            <div id="test-status" class="hidden">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="pulse-animation w-3 h-3 bg-yellow-500 rounded-full mr-3"></div>
                        <span class="text-yellow-800">Stress test is running...</span>
                    </div>
                    <div class="mt-2">
                        <div class="bg-yellow-200 rounded-full h-2">
                            <div id="progress-bar" class="bg-yellow-500 h-2 rounded-full transition-all duration-1000"
                                style="width: 0%"></div>
                        </div>
                        <p class="text-sm text-yellow-700 mt-1">
                            Time remaining: <span id="time-remaining">0</span> seconds
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 text-gray-500">
            <p>Last updated: <span id="last-updated">{{ now()->format('Y-m-d H:i:s') }}</span></p>
            <p class="mt-2">Auto refresh every 5 seconds</p>
        </div>
    </div>

    <script>
        // CSRF Token untuk AJAX requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Chart.js setup
        const ctx = document.getElementById('cpuChart').getContext('2d');
        const cpuChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'CPU Usage (%)',
                    data: [],
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Variables
        let isStressTestRunning = false;
        let stressTestInterval;
        let cpuUpdateInterval;

        // DOM Elements
        const intensitySlider = document.getElementById('intensity');
        const intensityValue = document.getElementById('intensity-value');
        const stressTestBtn = document.getElementById('stress-test-btn');
        const testStatus = document.getElementById('test-status');
        const progressBar = document.getElementById('progress-bar');
        const timeRemaining = document.getElementById('time-remaining');
        const cpuPercentage = document.getElementById('cpu-percentage');
        const lastUpdated = document.getElementById('last-updated');

        // Event Listeners
        intensitySlider.addEventListener('input', function() {
            intensityValue.textContent = this.value;
        });

        stressTestBtn.addEventListener('click', function() {
            if (!isStressTestRunning) {
                startStressTest();
            }
        });

        // Functions
        function updateCpuUsage() {
            fetch('/instance/cpu', {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    const usage = data.cpu_usage;
                    cpuPercentage.textContent = usage + '%';

                    // Update chart
                    const now = new Date().toLocaleTimeString();
                    cpuChart.data.labels.push(now);
                    cpuChart.data.datasets[0].data.push(usage);

                    // Keep only last 20 data points
                    if (cpuChart.data.labels.length > 20) {
                        cpuChart.data.labels.shift();
                        cpuChart.data.datasets[0].data.shift();
                    }

                    cpuChart.update('none');

                    // Update CPU meter
                    document.querySelector('.cpu-meter').style.background =
                        `conic-gradient(from 0deg, #ef4444 0deg ${usage * 3.6}deg, #e5e7eb ${usage * 3.6}deg 360deg)`;

                    lastUpdated.textContent = new Date().toLocaleString();
                })
                .catch(error => {
                    console.error('Error fetching CPU usage:', error);
                });
        }

        function startStressTest() {
            const duration = parseInt(document.getElementById('duration').value);
            const intensity = parseInt(document.getElementById('intensity').value);

            isStressTestRunning = true;
            stressTestBtn.disabled = true;
            stressTestBtn.textContent = 'Running...';
            stressTestBtn.classList.remove('bg-red-500', 'hover:bg-red-600');
            stressTestBtn.classList.add('bg-gray-500');
            testStatus.classList.remove('hidden');

            let timeLeft = duration;
            timeRemaining.textContent = timeLeft;

            // Progress bar animation
            const progressInterval = setInterval(() => {
                const progress = ((duration - timeLeft) / duration) * 100;
                progressBar.style.width = progress + '%';
                timeRemaining.textContent = timeLeft;

                if (timeLeft <= 0) {
                    clearInterval(progressInterval);
                    endStressTest();
                }
                timeLeft--;
            }, 1000);

            // Start stress test
            fetch('/instance/stress-cpu', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        duration: duration,
                        intensity: intensity
                    })
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Stress test completed:', data);
                })
                .catch(error => {
                    console.error('Error running stress test:', error);
                    endStressTest();
                });
        }

        function endStressTest() {
            isStressTestRunning = false;
            stressTestBtn.disabled = false;
            stressTestBtn.textContent = 'Start Stress Test';
            stressTestBtn.classList.remove('bg-gray-500');
            stressTestBtn.classList.add('bg-red-500', 'hover:bg-red-600');
            testStatus.classList.add('hidden');
            progressBar.style.width = '0%';
        }

        // Initialize
        updateCpuUsage();

        // Auto refresh CPU usage every 5 seconds
        cpuUpdateInterval = setInterval(updateCpuUsage, 5000);

        // Update server time every second
        setInterval(function() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', {
                hour12: false
            });
            document.getElementById('server-time').textContent = timeString;
        }, 1000);

        // Auto refresh page every 5 minutes to get fresh instance data
        setTimeout(() => {
            window.location.reload();
        }, 300000);

        // Debug Modal Functions
        function openDebugModal() {
            document.getElementById('debugModal').classList.remove('hidden');
            loadDebugInfo();
        }

        function closeDebugModal() {
            document.getElementById('debugModal').classList.add('hidden');
        }

        function loadDebugInfo() {
            const debugContent = document.getElementById('debugContent');
            debugContent.innerHTML =
                '<div class="text-center py-4"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div><p class="mt-2">Loading debug info...</p></div>';

            fetch('/instance/debug')
                .then(response => response.json())
                .then(data => {
                    displayDebugInfo(data);
                })
                .catch(error => {
                    debugContent.innerHTML =
                        '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"><strong>Error:</strong> ' +
                        error.message + '</div>';
                });
        }

        function displayDebugInfo(data) {
            const debugContent = document.getElementById('debugContent');
            let html = '';

            // Environment Detection
            html += '<div class="mb-6">';
            html += '<h3 class="text-lg font-semibold mb-3 text-gray-800">Environment Detection</h3>';
            html += '<div class="bg-' + (data.is_aws ? 'green' : 'red') + '-100 border border-' + (data.is_aws ? 'green' :
                'red') + '-400 text-' + (data.is_aws ? 'green' : 'red') + '-700 px-4 py-3 rounded">';
            html += '<strong>AWS Environment:</strong> ' + (data.is_aws ? 'YES' : 'NO');
            html += '</div>';
            html += '</div>';

            // Detection Methods
            html += '<div class="mb-6">';
            html += '<h3 class="text-lg font-semibold mb-3 text-gray-800">Detection Methods</h3>';
            html += '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';

            Object.entries(data.detection_methods).forEach(([method, result]) => {
                html += '<div class="bg-gray-50 p-3 rounded">';
                html += '<strong>' + method.replace(/_/g, ' ').toUpperCase() + ':</strong> ';
                html += '<span class="text-' + (result ? 'green' : 'red') + '-600">' + (result ? 'PASS' : 'FAIL') +
                    '</span>';
                html += '</div>';
            });

            html += '</div>';
            html += '</div>';

            // Raw Metadata
            if (data.raw_metadata) {
                html += '<div class="mb-6">';
                html += '<h3 class="text-lg font-semibold mb-3 text-gray-800">Raw Metadata Response</h3>';
                html += '<div class="bg-gray-100 p-4 rounded overflow-auto max-h-64">';
                html += '<pre class="text-sm">' + JSON.stringify(data.raw_metadata, null, 2) + '</pre>';
                html += '</div>';
                html += '</div>';
            }

            // Environment Variables
            if (data.environment_vars) {
                html += '<div class="mb-6">';
                html += '<h3 class="text-lg font-semibold mb-3 text-gray-800">AWS Environment Variables</h3>';
                html += '<div class="grid grid-cols-1 gap-2">';

                Object.entries(data.environment_vars).forEach(([key, value]) => {
                    html += '<div class="bg-blue-50 p-2 rounded">';
                    html += '<strong>' + key + ':</strong> ';
                    html += '<code class="text-sm">' + (value || 'Not Set') + '</code>';
                    html += '</div>';
                });

                html += '</div>';
                html += '</div>';
            }

            // System Info
            if (data.system_info) {
                html += '<div class="mb-6">';
                html += '<h3 class="text-lg font-semibold mb-3 text-gray-800">System Information</h3>';
                html += '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';

                Object.entries(data.system_info).forEach(([key, value]) => {
                    html += '<div class="bg-yellow-50 p-3 rounded">';
                    html += '<strong>' + key.replace(/_/g, ' ').toUpperCase() + ':</strong><br>';
                    html += '<code class="text-sm break-all">' + value + '</code>';
                    html += '</div>';
                });

                html += '</div>';
                html += '</div>';
            }

            debugContent.innerHTML = html;
        }
    </script>

    <!-- Debug Modal -->
    <div id="debugModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">AWS Detection Debug Information</h3>
                    <button onclick="closeDebugModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div id="debugContent" class="mt-4">
                    <!-- Debug content will be loaded here -->
                </div>
                <div class="flex justify-end mt-6">
                    <button onclick="closeDebugModal()"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                        Close
                    </button>
                    <button onclick="loadDebugInfo()"
                        class="ml-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                        Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
