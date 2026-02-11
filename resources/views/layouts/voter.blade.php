<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ 
          darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
          toggleTheme() {
              this.darkMode = !this.darkMode;
              localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
              if (this.darkMode) {
                  document.documentElement.classList.add('dark');
              } else {
                  document.documentElement.classList.remove('dark');
              }
          }
      }"
      x-init="$watch('darkMode', val => val ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark')); if(darkMode) document.documentElement.classList.add('dark');"
      class="h-full bg-gray-50 dark:bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        [x-cloak] { display: none !important; }
        
        /* Change active navigation selector from blue/indigo to amber */
        nav a.bg-indigo-600,
        nav a.bg-indigo-700,
        nav a.bg-indigo-800,
        nav a.bg-blue-600,
        nav a.bg-blue-700,
        nav a.bg-blue-800,
        nav button.bg-indigo-600,
        nav button.bg-indigo-700,
        nav button.bg-blue-600,
        nav button.bg-blue-700,
        .bg-indigo-600,
        .bg-indigo-700,
        .bg-blue-600,
        .bg-blue-700 {
            background-color: rgb(245 158 11) !important; /* amber-500 */
        }
        
        /* Hover states for amber */
        nav a.bg-indigo-600:hover,
        nav a.bg-blue-600:hover,
        nav button.bg-indigo-600:hover,
        nav button.bg-blue-600:hover {
            background-color: rgb(217 119 6) !important; /* amber-600 */
        }
        
        /* Text colors */
        nav a.text-indigo-600,
        nav a.text-blue-600,
        .text-indigo-600,
        .text-blue-600 {
            color: rgb(245 158 11) !important; /* amber-500 */
        }
        
        /* Ring/border colors */
        .ring-indigo-600,
        .ring-blue-600,
        .border-indigo-600,
        .border-blue-600 {
            border-color: rgb(245 158 11) !important;
            --tw-ring-color: rgb(245 158 11) !important;
        }
    </style>
</head>
<body class="h-full font-sans antialiased text-gray-900 dark:text-gray-100">
    <div x-data="{ sidebarOpen: false }">
        
        <!-- Mobile Sidebar (Off-canvas) -->
        <div x-show="sidebarOpen" class="relative z-50 lg:hidden" role="dialog" aria-modal="true" x-cloak>
            <div x-show="sidebarOpen" 
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/80" 
                 @click="sidebarOpen = false"></div>

            <div class="fixed inset-0 flex">
                <div x-show="sidebarOpen" 
                     x-transition:enter="transition ease-in-out duration-300 transform"
                     x-transition:enter-start="-translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transition ease-in-out duration-300 transform"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="-translate-x-full"
                     class="relative mr-16 flex w-full max-w-xs flex-1">
                    
                    <div class="absolute left-full top-0 flex w-16 justify-center pt-5">
                        <button type="button" class="-m-2.5 p-2.5" @click="sidebarOpen = false">
                            <span class="sr-only">Close sidebar</span>
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Sidebar Content (Mobile) -->
                    <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-white dark:bg-[#1e293b] px-6 pb-4 ring-1 ring-gray-200 dark:ring-white/10">
                        <div class="flex h-16 shrink-0 items-center">
                            @if(current_organization()->logo_path)
                                <img class="h-8 w-auto" src="{{ Storage::url(current_organization()->logo_path) }}" alt="{{ current_organization()->name }}">
                            @else
                                <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center text-white font-bold">
                                    {{ substr(current_organization()->name, 0, 1) }}
                                </div>
                            @endif
                            <span class="ml-4 text-gray-900 dark:text-white font-semibold truncate">{{ current_organization()->name }}</span>
                        </div>
                        <nav class="flex flex-1 flex-col">
                            <ul role="list" class="flex flex-1 flex-col gap-y-7">
                                <li>
                                    <ul role="list" class="-mx-2 space-y-1">
                                        @include('layouts.partials.voter-nav')
                                    </ul>
                                </li>
                                <li class="mt-auto">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="group -mx-2 flex w-full gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-700 hover:text-gray-900 dark:hover:text-white">
                                            <x-heroicon-o-arrow-right-on-rectangle class="h-6 w-6 shrink-0" />
                                            Sign out
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Static Sidebar (Desktop) -->
        <div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-64 lg:flex-col">
            <div class="flex grow flex-col gap-y-6 overflow-y-auto bg-white dark:bg-[#1e293b] px-4 pb-4 border-r border-gray-200 dark:border-slate-800">
                <!-- Logo/Brand Section -->
                <div class="flex h-20 shrink-0 items-center gap-3 pt-6 px-2">
                    @if(current_organization()->logo_path)
                        <img class="h-10 w-10 rounded-lg object-cover ring-2 ring-gray-200 dark:ring-white/10" src="{{ Storage::url(current_organization()->logo_path) }}" alt="{{ current_organization()->name }}">
                    @else
                        <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center text-white font-bold text-lg shadow-lg ring-2 ring-gray-200 dark:ring-white/10">
                            {{ substr(current_organization()->name, 0, 1) }}
                        </div>
                    @endif
                    <div class="flex flex-col min-w-0">
                         <span class="text-gray-900 dark:text-white font-bold text-base truncate">{{ current_organization()->name }}</span>
                    </div>
                </div>
                
                <!-- Navigation -->
                <nav class="flex flex-1 flex-col">
                    <ul role="list" class="space-y-1">
                        @include('layouts.partials.voter-nav')
                    </ul>
                </nav>
            </div>
        </div>

        <!-- Top Header - Full Width (Outside main content to span entire width) -->
        <div class="lg:pl-64">
            <div class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
                <button type="button" class="-m-2.5 p-2.5 text-gray-700 dark:text-gray-200 lg:hidden" @click="sidebarOpen = true">
                    <span class="sr-only">Open sidebar</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                <!-- Separator -->
                <div class="h-6 w-px bg-gray-200 dark:bg-slate-700 lg:hidden" aria-hidden="true"></div>

                <!-- Left side: Title/Breadcrumb -->
                <div class="flex items-center gap-2 flex-1">
                     <span class="font-semibold text-gray-900 dark:text-white lg:hidden">
                        {{ current_organization()->name }}
                     </span>
                </div>
                
                <!-- Right side: User info, Voter ID, Theme toggle, Logout -->
                <div class="flex items-center gap-3">
                    <!-- User Profile -->
                    <div class="hidden sm:flex items-center gap-3 px-3 py-1.5 bg-gray-100 dark:bg-slate-800 rounded-lg">
                        @if(auth()->user()->avatar)
                            <img class="h-7 w-7 rounded-full object-cover ring-2 ring-white/10" src="{{ auth()->user()->avatar }}" alt="">
                        @else
                            <div class="h-7 w-7 rounded-full bg-slate-700 flex items-center justify-center text-white border border-slate-600">
                                <span class="text-xs font-medium">{{ substr(auth()->user()->name, 0, 1) }}</span>
                            </div>
                        @endif
                        <div class="flex flex-col">
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ auth()->user()->name }}</span>
                            @if(auth()->user()->voter_id)
                                <span class="text-xs text-gray-500 dark:text-gray-400">ID: {{ auth()->user()->voter_id }}</span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Theme Toggle -->
                    <button @click="toggleTheme()" class="p-2 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 transition-all" title="Toggle Theme">
                        <x-heroicon-o-moon x-show="!darkMode" class="w-5 h-5" />
                        <x-heroicon-o-sun x-show="darkMode" class="w-5 h-5" style="display:none;" />
                    </button>
                    
                    <!-- Logout Button -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="p-2 text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 transition-all" title="Sign out">
                            <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5" />
                        </button>
                    </form>
                </div>
            </div>

            <!-- Main Content Area -->
            <main class="py-10">
                <div class="px-4 sm:px-6 lg:px-8">
                    @if(session('error'))
                        <div class="mb-6 rounded-md bg-red-50 dark:bg-red-900/30 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <x-heroicon-s-x-circle class="h-5 w-5 text-red-400" />
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Error</h3>
                                    <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                        <p>{{ session('error') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="mb-6 rounded-md bg-green-50 dark:bg-green-900/30 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <x-heroicon-s-check-circle class="h-5 w-5 text-green-400" />
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-green-800 dark:text-green-200">Success</h3>
                                    <div class="mt-2 text-sm text-green-700 dark:text-green-300">
                                        <p>{{ session('success') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>
    @livewireScripts
    @include('partials.tawk-to')
</body>
</html>