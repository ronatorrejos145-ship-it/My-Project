<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100 dark:bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'ISP Platform') - {{ config('app.name', 'ISP Platform') }}</title>

    <!-- Tailwind CSS CDN for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full font-sans antialiased text-slate-900 dark:text-slate-100 bg-slate-100 dark:bg-slate-950">
    <div class="min-h-full flex flex-col">
        <!-- Top Navigation Bar -->
        <nav class="bg-slate-900 text-white border-b border-slate-800 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center space-x-3">
                        <span class="text-xl font-black tracking-wider text-indigo-400">APEX<span class="text-white">ISP</span></span>
                        <span class="px-2 py-0.5 text-xs font-semibold bg-indigo-950 text-indigo-300 border border-indigo-800 rounded">PRODUCTION</span>
                    </div>

                    <div class="flex items-center space-x-4">
                        @auth
                            <div class="text-sm">
                                <span class="font-medium text-slate-200">{{ auth()->user()->name }}</span>
                                <span class="ml-2 px-2 py-0.5 text-xs font-semibold bg-slate-800 text-indigo-300 rounded border border-slate-700">
                                    {{ auth()->user()->roles->first()?->name ?? 'User' }}
                                </span>
                            </div>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="text-xs text-slate-400 hover:text-white px-3 py-1.5 border border-slate-700 rounded-lg hover:bg-slate-800 transition">
                                    Sign Out
                                </button>
                            </form>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Layout with Sidebar -->
        <div class="flex-1 flex max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 gap-6">
            <!-- Sidebar Navigation -->
            <aside class="w-64 flex-shrink-0 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-4 h-fit">
                <div class="space-y-6">
                    <div>
                        <p class="px-3 text-xs font-bold text-slate-400 uppercase tracking-wider">Main</p>
                        <div class="mt-2 space-y-1">
                            <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                                📊 Platform Dashboard
                            </a>
                        </div>
                    </div>

                    <div>
                        <p class="px-3 text-xs font-bold text-slate-400 uppercase tracking-wider">Applications & Coverage (Phase 5)</p>
                        <div class="mt-2 space-y-1 text-sm">
                            <a href="{{ route('admin.applications.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 font-medium text-indigo-600 dark:text-indigo-400">📝 Service Applications</a>
                            <a href="{{ route('admin.serviceability.check.form') }}" class="block px-3 py-1.5 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">📡 Serviceability Checker</a>
                        </div>
                    </div>

                    <div>
                        <p class="px-3 text-xs font-bold text-slate-400 uppercase tracking-wider">Catalog & Product (Phase 4)</p>
                        <div class="mt-2 space-y-1 text-sm">
                            <a href="{{ route('admin.packages.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">⚡ Internet Plans</a>
                            <a href="{{ route('admin.packages.categories.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">📁 Categories</a>
                            <a href="{{ route('admin.packages.promotions.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">🎁 Promotions</a>
                        </div>
                    </div>

                    <div>
                        <p class="px-3 text-xs font-bold text-slate-400 uppercase tracking-wider">Customer & CRM (Phase 3)</p>
                        <div class="mt-2 space-y-1 text-sm">
                            <a href="{{ route('admin.crm.dashboard') }}" class="block px-3 py-1.5 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">📈 CRM Dashboard</a>
                            <a href="{{ route('admin.customers.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">👤 Customer Directory</a>
                            <a href="{{ route('admin.leads.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">🎯 CRM Sales Leads</a>
                        </div>
                    </div>

                    <div>
                        <p class="px-3 text-xs font-bold text-slate-400 uppercase tracking-wider">Master Data (Phase 2)</p>
                        <div class="mt-2 space-y-1 text-sm">
                            <a href="{{ route('admin.companies.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">🏢 Companies</a>
                            <a href="{{ route('admin.branches.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">📍 Branches</a>
                            <a href="{{ route('admin.service-areas.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">🗺️ Service Areas</a>
                            <a href="{{ route('admin.network.nodes.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">📡 Network Infrastructure</a>
                            <a href="{{ route('admin.assets.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">📦 Assets & Tools</a>
                            <a href="{{ route('admin.warehouses.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">🏬 Warehouse Catalog</a>
                            <a href="{{ route('admin.finance.accounts.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">💼 Chart of Accounts</a>
                            <a href="{{ route('admin.number-sequences.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">🔢 Number Sequences</a>
                        </div>
                    </div>

                    <div>
                        <p class="px-3 text-xs font-bold text-slate-400 uppercase tracking-wider">Administration</p>
                        <div class="mt-2 space-y-1 text-sm">
                            <a href="{{ route('admin.users.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">👥 Users</a>
                            <a href="{{ route('admin.departments.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">🏛️ Departments</a>
                            <a href="{{ route('admin.employees.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">👔 Employees</a>
                            <a href="{{ route('admin.roles-permissions.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">🛡️ Roles & Permissions</a>
                            <a href="{{ route('admin.audit-logs.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">📋 Audit Logs</a>
                            <a href="{{ route('admin.settings.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">⚙️ Settings</a>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main Workspace Content -->
            <main class="flex-1 min-w-0">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
