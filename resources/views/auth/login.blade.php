<!DOCTYPE html>
@php
    $organization = current_organization();
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ 
          darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)
      }"
      x-init="if(darkMode) document.documentElement.classList.add('dark')"
      class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - {{ $organization->name }} Elections</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-slate-900 min-h-screen flex flex-col transition-colors duration-300">
    
    {{-- Background Effects --}}
    <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden">
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-indigo-500/10 dark:bg-indigo-500/5 rounded-full blur-[128px]"></div>
        <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-purple-500/10 dark:bg-purple-500/5 rounded-full blur-[128px]"></div>
    </div>

    {{-- Main Content --}}
    <div class="flex-1 flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8 relative z-10">
        
        {{-- Logo & Organization Name --}}
        <div class="text-center mb-8">
            @if($organization->logo_path)
                <img src="{{ Storage::url($organization->logo_path) }}" alt="{{ $organization->name }}" class="h-20 w-20 mx-auto rounded-2xl object-cover shadow-lg ring-1 ring-gray-900/10 dark:ring-white/10 mb-6">
            @else
                <div class="h-20 w-20 mx-auto rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-3xl shadow-lg mb-6">
                    {{ substr($organization->name, 0, 1) }}
                </div>
            @endif
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ $organization->name }}
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400 font-medium">
                E-Voting Portal
            </p>
        </div>

        {{-- Login Card --}}
        <div class="w-full max-w-md">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-gray-100 dark:border-slate-700 overflow-hidden">
                <div class="px-8 py-8">
                    
                    {{-- Error Message --}}
                    @if(session('error'))
                        <div class="mb-6 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800" role="alert">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-red-500 dark:text-red-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <p class="font-semibold text-red-800 dark:text-red-300 text-sm">Access Denied</p>
                                    <p class="text-red-700 dark:text-red-400 text-sm mt-1">{{ session('error') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Welcome Text --}}
                    <div class="text-center mb-8">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Welcome Back</h2>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Sign in to access your voter dashboard</p>
                    </div>

                    {{-- Google Sign In Button --}}
                    <a href="{{ route('auth.google') }}" class="group w-full flex items-center justify-center gap-3 py-4 px-6 bg-white dark:bg-slate-900 border-2 border-gray-200 dark:border-slate-600 rounded-xl font-semibold text-gray-700 dark:text-gray-200 hover:border-indigo-500 dark:hover:border-indigo-400 hover:shadow-lg transition-all duration-200">
                        <svg class="h-5 w-5" viewBox="0 0 24 24">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.84z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        <span>Continue with Google</span>
                    </a>

                    {{-- Divider --}}
                    <div class="relative my-8">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-200 dark:border-slate-700"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-4 bg-white dark:bg-slate-800 text-gray-500 dark:text-gray-400 font-medium">
                                Authorized Voters Only
                            </span>
                        </div>
                    </div>

                    {{-- Info Box --}}
                    <div class="p-4 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800/50">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-sm text-indigo-700 dark:text-indigo-300">
                                Use your registered email address to sign in. Only voters on the official voter list can access the voting portal.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-8 py-4 bg-gray-50 dark:bg-slate-900/50 border-t border-gray-100 dark:border-slate-700">
                    <p class="text-center text-xs text-gray-500 dark:text-gray-400">
                        By signing in, you agree to the election rules and guidelines.
                    </p>
                </div>
            </div>

            {{-- Security Badge --}}
            <div class="mt-6 flex items-center justify-center gap-2 text-gray-400 dark:text-gray-500">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <span class="text-xs font-medium">Secure • Anonymous • Verified</span>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <footer class="relative z-10 py-6 text-center">
        <p class="text-sm text-gray-400 dark:text-gray-500">
            &copy; {{ date('Y') }} {{ $organization->name }}. All rights reserved.
        </p>
    </footer>
</body>
</html>
