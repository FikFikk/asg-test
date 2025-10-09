<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Routes</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">🛣️ Application Routes</h1>
            <p class="text-gray-600">All registered routes in your application</p>
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

        <!-- Statistics -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">📊 Route Statistics</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ count($routes) }}</div>
                    <div class="text-blue-800">Total Routes</div>
                </div>
                <div class="bg-green-50 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $routes->where('methods', 'like', '%GET%')->count() }}</div>
                    <div class="text-green-800">GET Routes</div>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-yellow-600">{{ $routes->where('methods', 'like', '%POST%')->count() }}</div>
                    <div class="text-yellow-800">POST Routes</div>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ $routes->whereNotNull('name')->count() }}</div>
                    <div class="text-purple-800">Named Routes</div>
                </div>
            </div>
        </div>

        <!-- Search & Filter -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-64">
                    <input type="text" id="searchRoutes" placeholder="Search routes..." 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <select id="methodFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Methods</option>
                    <option value="GET">GET</option>
                    <option value="POST">POST</option>
                    <option value="PUT">PUT</option>
                    <option value="DELETE">DELETE</option>
                    <option value="PATCH">PATCH</option>
                </select>
                <button onclick="clearFilters()" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">Clear</button>
            </div>
        </div>

        <!-- Routes Table -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URI</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Middleware</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="routesTableBody">
                        @foreach($routes as $route)
                        <tr class="route-row">
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $methods = explode('|', $route['methods']);
                                    $methodColors = [
                                        'GET' => 'bg-green-100 text-green-800',
                                        'POST' => 'bg-blue-100 text-blue-800',
                                        'PUT' => 'bg-yellow-100 text-yellow-800',
                                        'DELETE' => 'bg-red-100 text-red-800',
                                        'PATCH' => 'bg-purple-100 text-purple-800',
                                        'HEAD' => 'bg-gray-100 text-gray-800',
                                        'OPTIONS' => 'bg-gray-100 text-gray-800'
                                    ];
                                @endphp
                                @foreach($methods as $method)
                                    @if(trim($method) !== 'HEAD')
                                        <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full {{ $methodColors[trim($method)] ?? 'bg-gray-100 text-gray-800' }} mr-1 mb-1">
                                            {{ trim($method) }}
                                        </span>
                                    @endif
                                @endforeach
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $route['uri'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($route['name'])
                                    <span class="inline-block px-2 py-1 text-xs font-semibold bg-indigo-100 text-indigo-800 rounded-full">
                                        {{ $route['name'] }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 break-all">{{ $route['action'] }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($route['middleware'])
                                    <div class="text-xs text-gray-600">
                                        @foreach(explode(', ', $route['middleware']) as $middleware)
                                            <span class="inline-block bg-gray-100 text-gray-700 px-2 py-1 rounded mr-1 mb-1">{{ $middleware }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Route Count -->
        <div class="mt-6 text-center text-gray-600">
            <p>Showing <span id="visibleRoutes">{{ count($routes) }}</span> of {{ count($routes) }} routes</p>
        </div>
    </div>

    <script>
        const searchInput = document.getElementById('searchRoutes');
        const methodFilter = document.getElementById('methodFilter');
        const routeRows = document.querySelectorAll('.route-row');
        const visibleRoutesSpan = document.getElementById('visibleRoutes');

        function filterRoutes() {
            const searchTerm = searchInput.value.toLowerCase();
            const selectedMethod = methodFilter.value;
            let visibleCount = 0;

            routeRows.forEach(row => {
                const uri = row.cells[1].textContent.toLowerCase();
                const name = row.cells[2].textContent.toLowerCase();
                const action = row.cells[3].textContent.toLowerCase();
                const methods = row.cells[0].textContent;

                const matchesSearch = uri.includes(searchTerm) || 
                                     name.includes(searchTerm) || 
                                     action.includes(searchTerm);
                
                const matchesMethod = !selectedMethod || methods.includes(selectedMethod);

                if (matchesSearch && matchesMethod) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            visibleRoutesSpan.textContent = visibleCount;
        }

        function clearFilters() {
            searchInput.value = '';
            methodFilter.value = '';
            filterRoutes();
        }

        searchInput.addEventListener('input', filterRoutes);
        methodFilter.addEventListener('change', filterRoutes);
    </script>
</body>
</html>