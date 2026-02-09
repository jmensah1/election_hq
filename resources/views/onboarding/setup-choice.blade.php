<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Successful - Elections HQ</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="h-full">
    <div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-lg text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-900/50 mb-6">
                <svg class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h2 class="text-3xl font-bold tracking-tight text-white mb-2">Payment Successful!</h2>
            <p class="text-slate-400">Thank you for subscribing to Elections HQ</p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-lg">
            <div class="bg-slate-900 py-8 px-6 shadow sm:rounded-lg border border-slate-800">
                <!-- Payment Summary -->
                <div class="mb-8 p-4 rounded-lg bg-green-900/20 border border-green-700/30">
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-slate-400">Amount Paid</dt>
                            <dd class="text-white font-medium">{{ $payment->formatted_amount }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-400">Plan</dt>
                            <dd class="text-white">{{ ucfirst($lead->plan_tier) }} ({{ ucfirst($lead->billing_cycle) }})</dd>
                        </div>
                    </dl>
                </div>

                <h3 class="text-lg font-semibold text-white text-center mb-2">What would you like to do next?</h3>
                <p class="text-sm text-slate-400 text-center mb-6">
                    Setting up can be a bit technical. Choose what works best for you.
                </p>

                <div class="space-y-4">
                    <!-- Self Setup Option -->
                    <a href="{{ route('onboarding.setup.form', ['payment' => $payment->id]) }}" 
                       class="block p-4 rounded-lg border-2 border-slate-700 hover:border-amber-500/50 bg-slate-800/50 transition-all group">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-amber-500/20 flex items-center justify-center">
                                <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-white group-hover:text-amber-400 transition-colors">Set Up Now</h4>
                                <p class="text-sm text-slate-400 mt-1">Complete a quick form to set up your organization and start using Elections HQ immediately.</p>
                            </div>
                            <svg class="w-5 h-5 text-slate-500 group-hover:text-amber-400 flex-shrink-0 mt-1 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </a>

                    <!-- Skip / Let Us Help Option -->
                    <form action="{{ route('onboarding.setup.skip', ['payment' => $payment->id]) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="w-full p-4 rounded-lg border border-slate-700 hover:border-slate-500 bg-slate-800/30 transition-all group text-left">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-slate-700 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-white">Let Us Help</h4>
                                    <p class="text-sm text-slate-400 mt-1">Our team will call you within 24 hours to set everything up for you.</p>
                                </div>
                            </div>
                        </button>
                    </form>
                </div>

                <p class="mt-6 text-center text-xs text-slate-500">
                    Need help? <a href="https://wa.me/233246955436" class="text-amber-500 hover:text-amber-400">Chat with us on WhatsApp</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
