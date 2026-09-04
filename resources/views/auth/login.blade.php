<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ config('app.name', 'ISP Platform') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-xl shadow-2xl overflow-hidden p-8">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-slate-800">{{ config('app.name', 'ISP Platform') }}</h1>
            <p class="text-sm text-slate-500 mt-1">ISP & WiFi Operations System</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-600 rounded text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-slate-700">Email Address</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                    class="mt-1 block w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                <input type="password" name="password" id="password" required
                    class="mt-1 block w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500">
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-sky-600 shadow-sm focus:ring-sky-500">
                    <span class="ml-2 text-sm text-slate-600">Remember me</span>
                </label>
            </div>

            <div>
                <button type="submit"
                    class="w-full py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-sky-600 hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 transition">
                    Sign In
                </button>
            </div>
        </form>
    </div>
</body>
</html>
