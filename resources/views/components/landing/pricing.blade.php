<div id="pricing" class="py-24 relative z-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">Simple, Transparent Pricing</h2>
            <p class="text-slate-400 max-w-2xl mx-auto">
                Choose the plan that fits your organization's size and needs. No hidden fees.
            </p>
            
            <!-- Billing Toggle -->
            <div class="mt-8 flex items-center justify-center gap-3">
                <span class="text-slate-400 text-sm font-medium" id="monthly-label">Monthly</span>
                <button type="button" onclick="toggleBilling()" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-slate-700 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 focus:ring-offset-slate-950" role="switch" aria-checked="false" id="billing-toggle">
                    <span class="sr-only">Use annual billing</span>
                    <span aria-hidden="true" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out translate-x-0" id="toggle-button"></span>
                </button>
                <span class="text-amber-400 text-sm font-medium" id="annual-label">
                    Annual <span class="inline-block px-2 py-0.5 text-xs bg-amber-500/20 text-amber-300 rounded-full ml-1">Save 15%</span>
                </span>
            </div>
        </div>

        <div class="grid md:grid-cols-4 gap-6">
            <!-- New Plan -->
            <div class="p-8 rounded-2xl bg-slate-900/50 backdrop-blur-sm border border-slate-800 hover:border-slate-600 transition-all flex flex-col">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-white">New</h3>
                    <div class="mt-2">
                        <div class="flex items-baseline gap-1 monthly-price">
                            <span class="text-4xl font-bold text-white">₵100</span>
                            <span class="text-sm text-slate-500">/mo</span>
                        </div>
                        <div class="hidden items-baseline gap-1 annual-price">
                            <span class="text-4xl font-bold text-white">₵85</span>
                            <span class="text-sm text-slate-500">/mo</span>
                        </div>
                        <div class="hidden mt-1 annual-price">
                            <span class="text-xs text-amber-400">₵1,020 billed annually</span>
                        </div>
                    </div>
                </div>
                <ul class="space-y-3 mb-8 flex-1 text-sm text-slate-300">
                    <li class="flex gap-2">
                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>Up to 300 Voters</span>
                    </li>
                    <li class="flex gap-2">
                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>1 Active Election</span>
                    </li>
                    <li class="flex gap-2">
                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>500 MB Storage</span>
                    </li>
                    <li class="flex gap-2">
                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>7-Day Audit Logs</span>
                    </li>
                    <li class="flex gap-2 text-slate-500">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        <span>Custom Domain</span>
                    </li>
                    <li class="flex gap-2 text-slate-500">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        <span>SMS Notifications</span>
                    </li>
                </ul>
                <a href="{{ route('onboarding.create', ['plan' => 'new']) }}" class="w-full py-2 px-4 rounded-lg bg-slate-800 hover:bg-slate-700 text-white font-medium text-center transition-colors">
                    Choose New
                </a>
            </div>

            <!-- Basic Plan -->
            <div class="p-8 rounded-2xl bg-slate-900/50 backdrop-blur-sm border border-slate-800 hover:border-amber-500/50 transition-all flex flex-col relative overflow-hidden">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-white">Basic</h3>
                    <div class="mt-2">
                        <div class="flex items-baseline gap-1 monthly-price">
                            <span class="text-4xl font-bold text-white">₵180</span>
                            <span class="text-sm text-slate-500">/mo</span>
                        </div>
                        <div class="hidden items-baseline gap-1 annual-price">
                            <span class="text-4xl font-bold text-white">₵153</span>
                            <span class="text-sm text-slate-500">/mo</span>
                        </div>
                        <div class="hidden mt-1 annual-price">
                            <span class="text-xs text-amber-400">₵1,836 billed annually</span>
                        </div>
                    </div>
                </div>
                <ul class="space-y-3 mb-8 flex-1 text-sm text-slate-300">
                    <li class="flex gap-2">
                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>Up to 500 Voters</span>
                    </li>
                    <li class="flex gap-2">
                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>3 Active Elections</span>
                    </li>
                    <li class="flex gap-2">
                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>1 GB Storage</span>
                    </li>
                    <li class="flex gap-2">
                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>Custom Domain</span>
                    </li>
                    <li class="flex gap-2">
                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>30-Day Audit Logs</span>
                    </li>
                    <li class="flex gap-2 text-slate-500">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        <span>SMS Notifications</span>
                    </li>
                </ul>
                <a href="{{ route('onboarding.create', ['plan' => 'basic']) }}" class="w-full py-2 px-4 rounded-lg bg-slate-800 hover:bg-slate-700 text-white font-medium text-center transition-colors">
                    Choose Basic
                </a>
            </div>

            <!-- Premium Plan -->
            <div class="p-8 rounded-2xl bg-slate-900/80 backdrop-blur-md border-2 border-amber-500/50 shadow-2xl shadow-amber-900/10 flex flex-col relative transform scale-105 z-10">
                <div class="absolute top-0 right-0 bg-amber-500 text-slate-950 text-xs font-bold px-3 py-1 rounded-bl-lg">POPULAR</div>
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-white">Premium</h3>
                    <div class="mt-2">
                        <div class="flex items-baseline gap-1 monthly-price">
                            <span class="text-4xl font-bold text-white">₵550</span>
                            <span class="text-sm text-slate-500">/mo</span>
                        </div>
                        <div class="hidden items-baseline gap-1 annual-price">
                            <span class="text-4xl font-bold text-white">₵468</span>
                            <span class="text-sm text-slate-500">/mo</span>
                        </div>
                        <div class="hidden mt-1 annual-price">
                            <span class="text-xs text-amber-400">₵5,610 billed annually</span>
                        </div>
                    </div>
                </div>
                <ul class="space-y-3 mb-8 flex-1 text-sm text-slate-300">
                    <li class="flex gap-2">
                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>Up to 2,000 Voters</span>
                    </li>
                    <li class="flex gap-2">
                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>Unlimited Elections</span>
                    </li>
                    <li class="flex gap-2">
                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>10 GB Storage</span>
                    </li>
                    <li class="flex gap-2">
                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>Custom Domain</span>
                    </li>
                    <li class="flex gap-2">
                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>SMS Notifications</span>
                    </li>
                    <li class="flex gap-2">
                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>1-Year Audit Logs</span>
                    </li>
                </ul>
                <a href="{{ route('onboarding.create', ['plan' => 'premium']) }}" class="w-full py-2 px-4 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-center transition-colors">
                    Choose Premium
                </a>
            </div>

            <!-- Enterprise Plan -->
            <div class="p-8 rounded-2xl bg-slate-900/50 backdrop-blur-sm border border-slate-800 hover:border-slate-600 transition-all flex flex-col">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-white">Enterprise</h3>
                    <div class="mt-2">
                        <div class="flex items-baseline gap-1 monthly-price">
                            <span class="text-4xl font-bold text-white">₵1,800</span>
                            <span class="text-sm text-slate-500">/mo</span>
                        </div>
                        <div class="hidden items-baseline gap-1 annual-price">
                            <span class="text-4xl font-bold text-white">₵1,530</span>
                            <span class="text-sm text-slate-500">/mo</span>
                        </div>
                        <div class="hidden mt-1 annual-price">
                            <span class="text-xs text-amber-400">₵18,360 billed annually</span>
                        </div>
                    </div>
                </div>
                <ul class="space-y-3 mb-8 flex-1 text-sm text-slate-300">
                    <li class="flex gap-2">
                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>Unlimited Voters</span>
                    </li>
                    <li class="flex gap-2">
                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>Unlimited Elections</span>
                    </li>
                    <li class="flex gap-2">
                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>Unlimited Storage</span>
                    </li>
                    <li class="flex gap-2">
                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>Dedicated Support</span>
                    </li>
                    <li class="flex gap-2">
                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>Unlimited Audit Logs</span>
                    </li>
                    <li class="flex gap-2">
                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>Priority Onboarding</span>
                    </li>
                </ul>
                <a href="{{ route('onboarding.create', ['plan' => 'enterprise']) }}" class="w-full py-2 px-4 rounded-lg bg-slate-800 hover:bg-slate-700 text-white font-medium text-center transition-colors">
                    Choose Enterprise
                </a>
            </div>
        </div>

        <!-- Trust Badge -->
        <div class="mt-12 text-center">
            <p class="text-sm text-slate-400">All plans include end-to-end encryption, Google SSO, and real-time results</p>
        </div>
    </div>
</div>

<!-- Pricing Toggle Script -->
<script>
    function toggleBilling() {
        const toggle = document.getElementById('billing-toggle');
        const toggleButton = document.getElementById('toggle-button');
        const monthlyPrices = document.querySelectorAll('.monthly-price');
        const annualPrices = document.querySelectorAll('.annual-price');
        
        const isAnnual = toggle.getAttribute('aria-checked') === 'true';
        const targetBilling = isAnnual ? 'monthly' : 'annual'; // Toggling FROM current state

        // Update all plan links
        const planLinks = document.querySelectorAll('a[href*="/get-started"]');
        planLinks.forEach(link => {
            const url = new URL(link.href);
            url.searchParams.set('billing', targetBilling);
            link.href = url.toString();
        });
        
        if (isAnnual) {
            // Switch to monthly
            toggle.setAttribute('aria-checked', 'false');
            toggle.classList.remove('bg-amber-500');
            toggle.classList.add('bg-slate-700');
            toggleButton.classList.remove('translate-x-5');
            toggleButton.classList.add('translate-x-0');
            
            monthlyPrices.forEach(el => {
                el.classList.remove('hidden');
                el.classList.add('flex');
            });
            annualPrices.forEach(el => {
                el.classList.add('hidden');
                el.classList.remove('flex');
            });
        } else {
            // Switch to annual
            toggle.setAttribute('aria-checked', 'true');
            toggle.classList.remove('bg-slate-700');
            toggle.classList.add('bg-amber-500');
            toggleButton.classList.remove('translate-x-0');
            toggleButton.classList.add('translate-x-5');
            
            monthlyPrices.forEach(el => {
                el.classList.add('hidden');
                el.classList.remove('flex');
            });
            annualPrices.forEach(el => {
                el.classList.remove('hidden');
                el.classList.add('flex');
            });
        }
    }
</script>
