<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Cancelled - Elections HQ</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="h-full">
    <div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-amber-900/50 mb-6">
                <svg class="h-8 w-8 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h2 class="text-3xl font-bold tracking-tight text-white mb-2">Payment Cancelled</h2>
            <p class="text-slate-400">
                Your payment was not completed.
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-slate-900 py-8 px-4 shadow sm:rounded-lg sm:px-10 border border-slate-800 text-center">
                @if(session('error'))
                    <div class="mb-6 p-4 rounded-lg bg-red-900/30 border border-red-700/50 text-red-400 text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                <p class="text-slate-300 mb-6">
                    If you cancelled by mistake or experienced an issue, you can try again or contact our support team for assistance.
                </p>
                
                <div class="space-y-3">
                    <a href="{{ route('onboarding.create') }}" class="inline-flex w-full justify-center rounded-md border border-transparent bg-amber-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 focus:ring-offset-slate-900 transition-colors">
                        Try Again
                    </a>
                    <a href="{{ url('/') }}" class="inline-flex w-full justify-center rounded-md border border-slate-700 bg-slate-800 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-slate-700 transition-colors">
                        Return Home
                    </a>
                </div>

                <p class="mt-6 text-xs text-slate-500">
                    Need help? <a href="https://wa.me/233246955436" class="text-amber-500 hover:text-amber-400">Chat with us on WhatsApp</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
