<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ session('setup_complete') ? 'Organization Created' : 'Application Received' }} - Elections HQ</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="h-full">
    <div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-900/50 mb-6">
                <svg class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            
            @if(session('setup_complete'))
                <h2 class="text-3xl font-bold tracking-tight text-white mb-2">Organization Created!</h2>
                <p class="text-slate-400">Your Elections HQ account is ready to use.</p>
            @elseif(session('skipped_setup'))
                <h2 class="text-3xl font-bold tracking-tight text-white mb-2">Payment Successful!</h2>
                <p class="text-slate-400">Our team will contact you to complete setup.</p>
            @else
                <h2 class="text-3xl font-bold tracking-tight text-white mb-2">Application Received!</h2>
                <p class="text-slate-400">Thank you for applying to Elections HQ.</p>
            @endif
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-slate-900 py-8 px-4 shadow sm:rounded-lg sm:px-10 border border-slate-800">
                
                @if(session('setup_complete'))
                    {{-- Organization Setup Complete --}}
                    @php $organization = session('organization'); @endphp
                    <div class="mb-6 p-4 rounded-lg bg-green-900/20 border border-green-700/30">
                        <h3 class="text-sm font-medium text-green-400 mb-3">Your Organization</h3>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-slate-400">Organization</dt>
                                <dd class="text-white font-medium">{{ $organization->name }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-400">Plan</dt>
                                <dd class="text-white">{{ ucfirst($organization->subscription_plan) }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="mb-6 p-4 rounded-lg bg-amber-900/20 border border-amber-700/30">
                        <h3 class="text-sm font-medium text-amber-400 mb-2">Your Login URL</h3>
                        <p class="text-white font-mono text-sm break-all">{{ session('login_url') }}</p>
                    </div>

                    <p class="text-slate-300 mb-6 text-center text-sm">
                        You can now log in using the email and password you just created.
                    </p>

                    <a href="{{ session('login_url') }}" target="_blank" 
                       class="inline-flex w-full justify-center rounded-md bg-amber-500 py-2 px-4 text-sm font-medium text-slate-950 shadow-sm hover:bg-amber-400 transition-colors">
                        Go to Admin Login
                    </a>

                @elseif(session('skipped_setup'))
                    {{-- Skipped Setup - Team Will Call --}}
                    @php $payment = session('payment'); @endphp
                    <div class="mb-6 p-4 rounded-lg bg-green-900/20 border border-green-700/30">
                        <h3 class="text-sm font-medium text-green-400 mb-3">Payment Details</h3>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-slate-400">Amount Paid</dt>
                                <dd class="text-white font-medium">{{ $payment->formatted_amount }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-400">Reference</dt>
                                <dd class="text-white font-mono text-xs">{{ $payment->reference }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="mb-6 p-4 rounded-lg bg-blue-900/20 border border-blue-700/30">
                        <div class="flex gap-3">
                            <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-sm text-blue-300">
                                Our team will call you within <strong>24 hours</strong> to set up your organization and get you started.
                            </p>
                        </div>
                    </div>

                    <p class="text-slate-300 mb-6 text-center text-sm">
                        A confirmation email has been sent to your inbox with your payment details.
                    </p>

                    <a href="{{ url('/') }}" 
                       class="inline-flex w-full justify-center rounded-md border border-slate-700 bg-slate-800 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-slate-700 transition-colors">
                        Return to Home
                    </a>

                @elseif(session('payment_failed'))
                    {{-- Payment Failed - Manual Follow Up --}}
                    <div class="mb-6 p-4 rounded-lg bg-amber-900/20 border border-amber-700/30">
                        <p class="text-amber-400 text-sm">
                            <strong>Note:</strong> We couldn't process your payment automatically. Our team will contact you to complete the payment manually.
                        </p>
                    </div>
                    <p class="text-slate-300 mb-6 text-center text-sm">
                        We have received your application. Our team will review it and contact you shortly.
                    </p>
                    <a href="{{ url('/') }}" 
                       class="inline-flex w-full justify-center rounded-md border border-slate-700 bg-slate-800 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-slate-700 transition-colors">
                        Return to Home
                    </a>

                @else
                    {{-- Generic Success --}}
                    <p class="text-slate-300 mb-6 text-center text-sm">
                        We have sent a confirmation email to your inbox. Our team will review your application and contact you shortly.
                    </p>
                    <a href="{{ url('/') }}" 
                       class="inline-flex w-full justify-center rounded-md border border-slate-700 bg-slate-800 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-slate-700 transition-colors">
                        Return to Home
                    </a>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
