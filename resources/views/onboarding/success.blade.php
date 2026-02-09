<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Application Received - Elections HQ</title>
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
            @if(session('payment'))
                <h2 class="text-3xl font-bold tracking-tight text-white mb-2">Payment Successful!</h2>
                <p class="text-slate-400">
                    Thank you for subscribing to Elections HQ.
                </p>
            @else
                <h2 class="text-3xl font-bold tracking-tight text-white mb-2">Application Received!</h2>
                <p class="text-slate-400">
                    Thank you for applying to Elections HQ.
                </p>
            @endif
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-slate-900 py-8 px-4 shadow sm:rounded-lg sm:px-10 border border-slate-800">
                @if(session('payment'))
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
                            @if(session('lead'))
                                @php $lead = session('lead'); @endphp
                                <div class="flex justify-between">
                                    <dt class="text-slate-400">Plan</dt>
                                    <dd class="text-white">{{ ucfirst($lead->plan_tier) }} ({{ ucfirst($lead->billing_cycle) }})</dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    <p class="text-slate-300 mb-6 text-center text-sm">
                        A confirmation email has been sent to your inbox. Our team will set up your organization and contact you with your login details within 24 hours.
                    </p>
                @elseif(session('payment_failed'))
                    <div class="mb-6 p-4 rounded-lg bg-amber-900/20 border border-amber-700/30">
                        <p class="text-amber-400 text-sm">
                            <strong>Note:</strong> We couldn't process your payment automatically. Our team will contact you to complete the payment manually.
                        </p>
                    </div>
                    <p class="text-slate-300 mb-6 text-center text-sm">
                        We have received your application. Our team will review it and contact you shortly to finalize your plan and payment details.
                    </p>
                @else
                    <p class="text-slate-300 mb-6 text-center text-sm">
                        We have sent a confirmation email to your inbox. Our team will review your application and contact you shortly to finalize your plan and payment details.
                    </p>
                @endif
                
                <a href="{{ url('/') }}" class="inline-flex w-full justify-center rounded-md border border-transparent bg-slate-800 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-slate-700 border border-slate-700 transition-colors">
                    Return to Home
                </a>
            </div>
        </div>
    </div>
</body>
</html>
