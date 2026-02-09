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
            <h2 class="text-3xl font-bold tracking-tight text-white mb-2">Application Received!</h2>
            <p class="text-slate-400">
                Thank you for applying to Elections HQ.
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-slate-900 py-8 px-4 shadow sm:rounded-lg sm:px-10 border border-slate-800 text-center">
                <p class="text-slate-300 mb-6">
                    We have sent a confirmation email to your inbox. Our team will review your application and contact you shortly to finalize your plan and payment details.
                </p>
                
                <a href="{{ url('/') }}" class="inline-flex w-full justify-center rounded-md border border-transparent bg-slate-800 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-slate-700 border border-slate-700 transition-colors">
                    Return to Home
                </a>
            </div>
        </div>
    </div>
</body>
</html>
