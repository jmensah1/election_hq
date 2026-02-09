<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Get Started - Elections HQ</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="h-full">
    <div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <a href="{{ url('/') }}" class="flex justify-center items-center gap-2 mb-6">
                 <img src="{{ asset('images/logo.png') }}" alt="Elections HQ" class="h-10 w-auto">
                 <span class="font-bold text-xl text-white">Elections<span class="text-amber-500">HQ</span></span>
            </a>
            <h2 class="mt-6 text-center text-3xl font-bold tracking-tight text-white">Get started with {{ ucfirst($plan) }}</h2>
            <p class="mt-2 text-center text-sm text-slate-400">
                Billing: {{ ucfirst($billing) }}
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-slate-900 py-8 px-4 shadow sm:rounded-lg sm:px-10 border border-slate-800">
                <form class="space-y-6" action="{{ route('onboarding.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plan_tier" value="{{ $plan }}">
                    <input type="hidden" name="billing_cycle" value="{{ $billing }}">

                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-300">Contact Name</label>
                        <div class="mt-1">
                            <input id="name" name="name" type="text" autocomplete="name" required class="block w-full appearance-none rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-white placeholder-slate-500 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-amber-500 sm:text-sm" value="{{ old('name') }}">
                        </div>
                        @error('name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="organization_name" class="block text-sm font-medium text-slate-300">Organization Name</label>
                        <div class="mt-1">
                            <input id="organization_name" name="organization_name" type="text" required class="block w-full appearance-none rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-white placeholder-slate-500 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-amber-500 sm:text-sm" value="{{ old('organization_name') }}">
                        </div>
                        <p class="mt-1 text-xs text-slate-400">If you don't have an organization name, you can repeat your name.</p>
                        @error('organization_name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-300">Email address</label>
                        <div class="mt-1">
                            <input id="email" name="email" type="email" autocomplete="email" required class="block w-full appearance-none rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-white placeholder-slate-500 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-amber-500 sm:text-sm" value="{{ old('email') }}">
                        </div>
                        @error('email') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-slate-300">Phone Number</label>
                        <div class="mt-1">
                            <input id="phone" name="phone" type="tel" autocomplete="tel" required class="block w-full appearance-none rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-white placeholder-slate-500 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-amber-500 sm:text-sm" value="{{ old('phone') }}">
                        </div>
                        @error('phone') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                         <label for="plan_display" class="block text-sm font-medium text-slate-300">Selected Plan</label>
                         <div class="mt-1">
                             <input type="text" disabled class="block w-full appearance-none rounded-md border border-slate-700 bg-slate-800 px-3 py-2 text-slate-400 shadow-sm sm:text-sm cursor-not-allowed" value="{{ ucfirst($plan) }} ({{ ucfirst($billing) }})">
                         </div>
                    </div>

                    <div>
                        <button type="submit" class="flex w-full justify-center rounded-md border border-transparent bg-amber-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 focus:ring-offset-slate-900">
                            Submit Application
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
