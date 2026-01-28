<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elections HQ - Tenancy Debugger</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-10 font-mono text-sm">
    <div class="max-w-4xl mx-auto bg-white shadow rounded-lg overflow-hidden">
        <div class="bg-blue-600 px-6 py-4 border-b border-blue-700 flex justify-between items-center">
            <h1 class="text-xl font-bold text-white">Tenancy Debugger</h1>
            <span class="text-blue-100">Phase 3 Verification</span>
        </div>
        
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Request Info -->
            <div>
                <h3 class="text-gray-500 uppercase tracking-wider text-xs font-bold mb-3">Request Context</h3>
                <div class="bg-gray-50 rounded border p-4 space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Host:</span>
                        <span class="font-bold text-gray-900">{{ request()->getHost() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">App URL:</span>
                        <span class="font-bold text-gray-900">{{ config('app.url') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">App Timezone:</span>
                        <span class="font-bold text-gray-900">{{ config('app.timezone') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">PHP Timezone:</span>
                        <span class="font-bold text-gray-900">{{ date_default_timezone_get() }}</span>
                    </div>
                </div>
            </div>

            <!-- Organization Info -->
            <div>
                <h3 class="text-gray-500 uppercase tracking-wider text-xs font-bold mb-3">Resolved Organization</h3>
                @if(current_organization())
                    <div class="bg-green-50 rounded border border-green-200 p-4 space-y-2">
                        <div class="flex justify-between">
                            <span class="text-green-700">Status:</span>
                            <span class="bg-green-100 text-green-800 px-2 py-0.5 rounded text-xs font-bold uppercase">Resolved</span>
                        </div>
                        <div class="border-t border-green-100 my-2"></div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">ID:</span>
                            <span class="font-bold">{{ current_organization()->id }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Name:</span>
                            <span class="font-bold">{{ current_organization()->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subdomain:</span>
                            <span class="font-bold">{{ current_organization()->subdomain }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Timezone:</span>
                            <span class="font-bold">{{ current_organization()->timezone }}</span>
                        </div>
                    </div>
                @else
                    <div class="bg-yellow-50 rounded border border-yellow-200 p-4">
                        <div class="flex justify-between mb-2">
                            <span class="text-yellow-700">Status:</span>
                            <span class="bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded text-xs font-bold uppercase">Not Resolved</span>
                        </div>
                        <p class="text-yellow-800 text-xs">
                            No organization matched for this host. This is expected if you are on the root domain (landing page) or an unknown subdomain.
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Middleware Status -->
        <div class="px-6 pb-6">
            <h3 class="text-gray-500 uppercase tracking-wider text-xs font-bold mb-3">Middleware Check</h3>
            <div class="bg-gray-50 border rounded p-4">
                <code class="text-xs text-blue-600">SetOrganizationContext</code> ran: 
                <span class="font-bold {{ app()->has('current_organization') || current_organization() ? 'text-green-600' : 'text-gray-400' }}">
                    {{ app()->has('current_organization') ? 'YES (Bound)' : 'NO (Or not bound)' }}
                </span>
            </div>
        </div>
    </div>
</body>
</html>
