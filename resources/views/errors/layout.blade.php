<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - {{ config('app.name', 'Elections HQ') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="h-full antialiased bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 flex items-center justify-center min-h-screen">
    <div class="max-w-xl w-full px-6 py-12 text-center">
        <div class="mb-8 flex justify-center">
            @yield('icon')
        </div>
        
        <h1 class="text-4xl font-bold tracking-tight mb-4">
            @yield('code')
        </h1>
        
        <h2 class="text-2xl font-semibold mb-6">
            @yield('message')
        </h2>
        
        <p class="text-gray-500 dark:text-gray-400 mb-10 text-lg">
            @yield('description')
        </p>
        
        <div class="flex justify-center gap-4">
            <a href="javascript:history.back()" class="inline-flex items-center justify-center rounded-lg bg-gray-900 dark:bg-primary-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-gray-800 dark:hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-primary-600 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                Go Back
            </a>
            @yield('actions')
        </div>
    </div>
</body>
</html>
