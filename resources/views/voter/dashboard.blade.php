<x-voter-layout>
    <x-slot name="title">Dashboard - {{ config('app.name') }}</x-slot>

    <div class="max-w-4xl mx-auto">
        <!-- Dashboard Header -->
        <div class="mb-10 text-center">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-3 font-serif tracking-tight">
                Voter Dashboard
            </h1>
            <p class="text-gray-500 dark:text-gray-400 text-lg">
                View active ballots and upcoming elections for <span class="text-indigo-600 dark:text-indigo-400 font-semibold">{{ current_organization()->name }}</span>
            </p>
        </div>

        <!-- Active Elections Card -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden transition-all duration-300 hover:shadow-md">
            <!-- Header -->
            <div class="bg-gray-50/50 dark:bg-slate-800/50 border-b border-gray-100 dark:border-slate-700 px-8 py-6 flex items-center justify-between">
                <div>
                     <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        Current Elections
                    </h2>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">
                    Active
                </span>
            </div>

            <div class="p-8">
                 <div class="text-center py-12">
                    <div class="bg-gray-50 dark:bg-slate-700/50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">No elections active</h3>
                    <p class="mt-1 text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                        There are currently no active ballots available for your voting group. Please check back later or check your email for notifications.
                    </p>
                </div>
            </div>
        </div>

        <!-- Additional Info / Help (Optional) -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
             <div class="bg-indigo-50 dark:bg-indigo-900/10 rounded-xl p-6 border border-indigo-100 dark:border-indigo-500/10">
                <h3 class="font-semibold text-indigo-900 dark:text-indigo-300 mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Need Help?
                </h3>
                <p class="text-sm text-indigo-700 dark:text-indigo-400/80">
                    If you believe you should have access to a ballot but don't see it here, please contact your organization's election administrator securely.
                </p>
            </div>
             <div class="bg-amber-50 dark:bg-amber-900/10 rounded-xl p-6 border border-amber-100 dark:border-amber-500/10">
                <h3 class="font-semibold text-amber-900 dark:text-amber-400 mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                    Security Notice
                </h3>
                <p class="text-sm text-amber-700 dark:text-amber-500/80">
                    Your vote is fully anonymous. The system architecture prevents anyone, including admins, from linking your ballot choices back to your user identity.
                </p>
            </div>
        </div>
    </div>
</x-voter-layout>
