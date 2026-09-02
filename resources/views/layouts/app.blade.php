<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'ISP WiFi Platform') }} - @yield('title', 'Management')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans antialiased text-gray-900">
    <div class="min-h-screen flex flex-col md:flex-row">
        <!-- Sidebar Navigation -->
        <aside class="w-full md:w-64 bg-slate-900 text-white flex-shrink-0">
            <div class="p-4 border-b border-slate-800 flex justify-between items-center">
                <div>
                    <h1 class="text-lg font-bold text-sky-400">{{ config('app.name', 'ISP Platform') }}</h1>
                    <p class="text-xs text-slate-400">ISP Operations Management</p>
                </div>
            </div>

            <nav class="p-4 space-y-1">
                <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-sky-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                    Dashboard
                </a>

                @can('viewAny', App\Models\User::class)
                <div class="pt-3 pb-1">
                    <p class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Administration</p>
                </div>
                <a href="{{ route('admin.users.index') }}" class="block px-3 py-2 rounded text-sm font-medium {{ request()->routeIs('admin.users.*') ? 'bg-sky-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                    Users
                </a>
                <a href="{{ route('admin.roles.index') }}" class="block px-3 py-2 rounded text-sm font-medium {{ request()->routeIs('admin.roles.*') ? 'bg-sky-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                    Roles & Permissions
                </a>
                <a href="{{ route('admin.departments.index') }}" class="block px-3 py-2 rounded text-sm font-medium {{ request()->routeIs('admin.departments.*') ? 'bg-sky-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                    Departments
                </a>
                @endcan

                @can('viewAny', App\Models\Employee::class)
                <a href="{{ route('admin.employees.index') }}" class="block px-3 py-2 rounded text-sm font-medium {{ request()->routeIs('admin.employees.*') ? 'bg-sky-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                    Employees
                </a>
                @endcan

                @can('viewAny', App\Models\Customer::class)
                <div class="pt-3 pb-1">
                    <p class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">CRM & Operations</p>
                </div>
                <a href="{{ route('admin.customers.index') }}" class="block px-3 py-2 rounded text-sm font-medium {{ request()->routeIs('admin.customers.*') ? 'bg-sky-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                    Customers
                </a>
                @endcan

                <div class="pt-3 pb-1">
                    <p class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">System</p>
                </div>
                @if(Auth::user()->hasPermission('audit.view'))
                <a href="{{ route('admin.audit-logs.index') }}" class="block px-3 py-2 rounded text-sm font-medium {{ request()->routeIs('admin.audit-logs.*') ? 'bg-sky-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                    Audit Logs
                </a>
                @endif

                @if(Auth::user()->hasPermission('settings.manage'))
                <a href="{{ route('admin.settings.index') }}" class="block px-3 py-2 rounded text-sm font-medium {{ request()->routeIs('admin.settings.*') ? 'bg-sky-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                    Settings
                </a>
                @endif
            </nav>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top Navbar -->
            <header class="bg-white border-b border-gray-200 px-6 py-3 flex justify-between items-center">
                <div class="font-semibold text-gray-700">
                    @yield('header', 'Dashboard')
                </div>

                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">
                        {{ Auth::user()->name }}
                        <span class="text-xs bg-slate-200 text-slate-800 px-2 py-0.5 rounded font-mono">
                            {{ Auth::user()->roles->pluck('name')->implode(', ') ?: 'User' }}
                        </span>
                    </span>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-sm bg-red-50 text-red-600 hover:bg-red-100 px-3 py-1.5 rounded font-medium transition">
                            Logout
                        </button>
                    </form>
                </div>
            </header>

            <!-- Content -->
            <main class="p-6 flex-1">
                @if(session('success'))
                    <div class="mb-4 bg-emerald-50 border-l-4 border-emerald-500 p-4 text-emerald-700 text-sm rounded">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 text-red-700 text-sm rounded">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
