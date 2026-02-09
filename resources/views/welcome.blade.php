<!DOCTYPE html>
@php
    // Get organization from subdomain
    $organization = null;
    $host = request()->getHost();
    
    // Use configured base domain, or auto-detect from current request
    $baseDomain = config('app.base_domain');
    
    if (!$baseDomain) {
        // Auto-detect: if host is subdomain.example.com, base is example.com
        $parts = explode('.', $host);
        $baseDomain = count($parts) >= 2 ? implode('.', array_slice($parts, -2)) : $host;
    }
    
    if (str_ends_with($host, '.' . $baseDomain)) {
        $subdomain = str_replace('.' . $baseDomain, '', $host);
        if ($subdomain && $subdomain !== 'www') {
            $organization = \App\Models\Organization::where('subdomain', $subdomain)->first();
        }
    }
    
    $brandName = $organization?->name ?? 'Elections HQ';
    $logoUrl = $organization?->logo_path ? asset('storage/' . $organization->logo_path) : null;
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $brandName }} - Secure, Anonymous, Verified</title>

    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#f59e0b">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;1,600&display=swap" rel="stylesheet">

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <!-- Fallback or inline styles if build missing during dev -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['Inter', 'sans-serif'],
                            serif: ['Playfair Display', 'serif'],
                        },
                        colors: {
                            navy: {
                                900: '#0f172a',
                                800: '#1e293b',
                                700: '#334155',
                            },
                            gold: {
                                400: '#fbbf24',
                                500: '#f59e0b',
                                600: '#d97706',
                            }
                        }
                    }
                }
            }
        </script>
    @endif
</head>
<body class="antialiased bg-slate-950 text-slate-200 font-sans selection:bg-amber-500 selection:text-white flex flex-col min-h-screen">

    <!-- Global Background Effects -->
    <div class="fixed inset-0 z-0 pointer-events-none">
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-blue-600/20 rounded-full blur-[128px]"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-purple-600/10 rounded-full blur-[128px]"></div>
        <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-amber-600/10 rounded-full blur-[128px]"></div>
        <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 brightness-100 contrast-150"></div>
        <!-- Grid Pattern -->
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#80808012_1px,transparent_1px),linear-gradient(to_bottom,#80808012_1px,transparent_1px)] bg-[size:24px_24px]"></div>
    </div>

    <!-- Navigation -->
    <nav class="fixed w-full z-50 transition-all duration-300 bg-slate-950/80 backdrop-blur-md border-b border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center gap-3">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $brandName }}" class="h-10 w-auto object-contain">
                        <span class="font-bold text-xl tracking-tight text-white">{{ $brandName }}</span>
                    @else
                        <img src="{{ asset('images/logo.png') }}" alt="Elections HQ" class="h-10 w-auto object-contain">
                        <span class="font-bold text-xl tracking-tight text-white">
                            Elections<span class="text-amber-500">HQ</span>
                        </span>
                    @endif
                </div>

                <!-- Auth Links / Navigation -->
                <div class="flex items-center gap-6">
                    @if($organization)
                        <!-- Tenant Subdomain: Portal Access -->
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ route('voter.elections.index') }}" class="text-sm font-medium text-slate-300 hover:text-white transition-colors">Voting Portal</a>
                                <a href="{{ route('candidate.portal') }}" class="text-sm font-medium text-slate-300 hover:text-white transition-colors">Candidate Portal</a>
                            @else
                                <a href="{{ route('auth.google') }}" class="group relative px-6 py-2.5 bg-white text-slate-900 font-semibold text-sm rounded-full transition-all hover:bg-amber-50 hover:shadow-[0_0_20px_rgba(255,255,255,0.3)] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-900 focus:ring-white">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.84z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                                    </span>
                                </a>
                            @endauth
                        @endif
                    @else
                        <!-- Main Domain: Marketing Navigation -->
                        <a href="#pricing" class="text-sm font-medium text-slate-300 hover:text-white transition-colors">Pricing</a>
                        <a href="#contact" class="text-sm font-medium text-slate-300 hover:text-white transition-colors">Contact Us</a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative flex-grow flex items-center justify-center overflow-hidden pt-20">
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            @if($organization)
                <!-- Vendor-specific welcome -->
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $brandName }}" class="h-24 w-auto mx-auto mb-8 object-contain">
                @endif
                <h1 class="text-5xl md:text-7xl font-bold tracking-tight text-white mb-6 leading-tight">
                    Welcome to <br/>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-200 via-amber-400 to-amber-600">{{ $brandName }}</span>
                    <br><span class="text-3xl md:text-4xl font-medium text-transparent bg-clip-text bg-gradient-to-r from-amber-300 to-amber-500">Election Portal</span>
                </h1>
                
                <p class="mt-4 max-w-2xl mx-auto text-xl text-slate-400 leading-relaxed mb-10">
                    Access your elections portal securely. Vote with confidence knowing your ballot is anonymous, verified, and counted.
                </p>
            @else
                <!-- Platform landing page -->
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-800/50 border border-slate-700/50 text-amber-400 text-xs font-semibold tracking-wide uppercase mb-8 backdrop-blur-sm animate-fade-in-up">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                    The Standard for Modern Democracy
                </div>
                
                <h1 class="text-5xl md:text-7xl font-bold tracking-tight text-white mb-6 leading-tight">
                    Secure. Anonymous. <br/>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-200 via-amber-400 to-amber-600">Verifiable.</span>
                </h1>
                
                <p class="mt-4 max-w-2xl mx-auto text-xl text-slate-400 leading-relaxed mb-10">
                    Elections HQ provides an audit-grade electronic voting platform designed for integrity. 
                    Decoupled architecture ensures complete voter anonymity while maintaining mathematical proof of inclusion.
                </p>
            @endif

            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                @if($organization)
                    @auth
                        <a href="{{ route('voter.elections.index') }}" class="px-8 py-4 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-lg transition-all transform hover:scale-105 shadow-[0_0_40px_rgba(245,158,11,0.4)]">
                            Go to Voting Portal
                        </a>
                        <a href="{{ route('candidate.portal') }}" class="px-8 py-4 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-lg transition-all transform hover:scale-105 border border-slate-700">
                            Candidate Portal
                        </a>
                    @else
                        <a href="{{ route('auth.google') }}" class="px-8 py-4 bg-white text-slate-900 font-bold rounded-lg transition-all transform hover:scale-105 hover:bg-slate-100 shadow-xl">
                            Access Voter Portal
                        </a>
                    @endauth
                @else
                    <a href="#pricing" class="px-8 py-4 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-lg transition-all transform hover:scale-105 shadow-[0_0_40px_rgba(245,158,11,0.4)]">
                        View Pricing
                    </a>
                    <a href="#features" class="px-8 py-4 bg-slate-800/50 hover:bg-slate-800 text-white font-semibold rounded-lg border border-slate-700 backdrop-blur-sm transition-all hover:border-slate-500">
                        Learn How it Works
                    </a>
                @endif
            </div>

            @unless($organization)
                <!-- Stats/Social Proof - Only on main landing page -->
                <div class="mt-16 pt-8 border-t border-white/5 grid grid-cols-2 md:grid-cols-4 gap-8">
                    <div>
                        <div class="text-3xl font-bold text-white">100%</div>
                        <div class="text-sm text-slate-500 mt-1 uppercase tracking-wider">Anonymity</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-white">Zero</div>
                        <div class="text-sm text-slate-500 mt-1 uppercase tracking-wider">Correlation</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-white">24/7</div>
                        <div class="text-sm text-slate-500 mt-1 uppercase tracking-wider">Availability</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-white">Audit</div>
                        <div class="text-sm text-slate-500 mt-1 uppercase tracking-wider">Ready Logs</div>
                    </div>
                </div>
            @endunless
        </div>
    </div>

    @unless($organization)
        <!-- Features Section - Only on main landing page -->
        <div id="features" class="py-24 relative z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- ... existing features content ... -->
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">Enterprise-Grade Integrity</h2>
                    <p class="text-slate-400 max-w-2xl mx-auto">
                        Built to solve the paradox of electronic voting: proving a vote was counted without revealing who cast it.
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="p-8 rounded-2xl bg-slate-900/50 backdrop-blur-sm border border-slate-800 hover:border-amber-500/30 transition-all hover:bg-slate-900/80 group">
                        <div class="w-12 h-12 bg-slate-900 rounded-lg flex items-center justify-center mb-6 group-hover:scale-110 transition-transform ring-1 ring-slate-800 group-hover:ring-amber-500/50">
                            <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">Decoupled Architecture</h3>
                        <p class="text-slate-400 leading-relaxed">
                            We separate <strong>WHO</strong> voted from <strong>WHAT</strong> they voted for into two completely isolated database tables. There is zero mathematical link between your identity and your ballot choice.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="p-8 rounded-2xl bg-slate-900/50 backdrop-blur-sm border border-slate-800 hover:border-blue-500/30 transition-all hover:bg-slate-900/80 group">
                        <div class="w-12 h-12 bg-slate-900 rounded-lg flex items-center justify-center mb-6 group-hover:scale-110 transition-transform ring-1 ring-slate-800 group-hover:ring-blue-500/50">
                            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">Immutable Audit Logs</h3>
                        <p class="text-slate-400 leading-relaxed">
                            Every system action is recorded in a tamper-evident audit trail. Admins can verify election integrity without ever compromising voter secrecy.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="p-8 rounded-2xl bg-slate-900/50 backdrop-blur-sm border border-slate-800 hover:border-green-500/30 transition-all hover:bg-slate-900/80 group">
                        <div class="w-12 h-12 bg-slate-900 rounded-lg flex items-center justify-center mb-6 group-hover:scale-110 transition-transform ring-1 ring-slate-800 group-hover:ring-green-500/50">
                            <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">Multi-Tenant Ready</h3>
                        <p class="text-slate-400 leading-relaxed">
                            One platform, infinite organizations. Securely isolated environments for universities, unions, and corporate boards with custom domains and branding.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pricing Section -->
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
                                <div class="mt-2 flex items-baseline gap-1">
                                    <span class="text-4xl font-bold text-white">₵100</span>
                                    <span class="text-sm text-slate-500">/month</span>
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
                            <a href="{{ route('onboarding.create', ['plan' => 'new']) }}" class="w-full py-2 px-4 rounded-lg border border-slate-700 hover:bg-slate-800 text-white font-medium text-center transition-colors">
                                Get Started
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
                            <a href="{{ route('onboarding.create', ['plan' => 'enterprise']) }}" class="w-full py-2 px-4 rounded-lg border border-slate-700 hover:bg-slate-800 text-white font-medium text-center transition-colors">
                                Contact Sales
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
        <!-- Contact Section -->
        <div id="contact" class="py-24 relative z-10 bg-slate-900/30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid md:grid-cols-2 gap-16 items-center">
                    <div>
                        <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">Get in Touch</h2>
                        <p class="text-slate-400 text-lg mb-8">
                            Have questions about Elections HQ? We're here to help. Reach out to us for enquiries, support, or to schedule a demo.
                        </p>

                        <div class="space-y-6">
                            <!-- Phone -->
                            <a href="tel:0246955436" class="flex items-center gap-4 group p-4 rounded-xl bg-slate-800/50 border border-slate-700 hover:border-amber-500/50 transition-all">
                                <div class="w-12 h-12 rounded-full bg-slate-900 flex items-center justify-center group-hover:scale-110 transition-transform text-amber-500">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                </div>
                                <div>
                                    <div class="text-sm text-slate-500 font-medium">Call Us</div>
                                    <div class="text-lg font-bold text-white">024 695 5436</div>
                                </div>
                            </a>

                            <!-- WhatsApp -->
                            <a href="https://wa.me/233246955436" target="_blank" class="flex items-center gap-4 group p-4 rounded-xl bg-slate-800/50 border border-slate-700 hover:border-green-500/50 transition-all">
                                <div class="w-12 h-12 rounded-full bg-slate-900 flex items-center justify-center group-hover:scale-110 transition-transform text-green-500">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                                </div>
                                <div>
                                    <div class="text-sm text-slate-500 font-medium">Chat on WhatsApp</div>
                                    <div class="text-lg font-bold text-white">024 695 5436</div>
                                </div>
                            </a>

                            <!-- Email -->
                            <a href="mailto:joseph.mensah@jbmensah.com" class="flex items-center gap-4 group p-4 rounded-xl bg-slate-800/50 border border-slate-700 hover:border-blue-500/50 transition-all">
                                <div class="w-12 h-12 rounded-full bg-slate-900 flex items-center justify-center group-hover:scale-110 transition-transform text-blue-500">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                </div>
                                <div>
                                    <div class="text-sm text-slate-500 font-medium">Email Us</div>
                                    <div class="text-sm font-bold text-white break-all">joseph.mensah@jbmensah.com</div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Contact Form -->
                    <div class="bg-slate-900 rounded-2xl p-8 border border-slate-800 shadow-2xl">
                        @if(session('success'))
                            <div class="mb-6 p-4 rounded-lg bg-green-500/10 border border-green-500/20 text-green-400 flex items-center gap-3">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                {{ session('success') }}
                            </div>
                        @endif

                        <form action="{{ route('contact.store') }}" method="POST" class="space-y-6">
                            @csrf
                            <div>
                                <label for="name" class="block text-sm font-medium text-slate-300 mb-2">Your Name</label>
                                <input type="text" name="name" id="name" required class="w-full px-4 py-3 rounded-lg bg-slate-950 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all" placeholder="John Doe">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="email" class="block text-sm font-medium text-slate-300 mb-2">Email Address</label>
                                    <input type="email" name="email" id="email" required class="w-full px-4 py-3 rounded-lg bg-slate-950 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all" placeholder="john@example.com">
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-slate-300 mb-2">Phone Number</label>
                                    <input type="tel" name="phone" id="phone" class="w-full px-4 py-3 rounded-lg bg-slate-950 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all" placeholder="Optional">
                                    @error('phone')
                                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label for="message" class="block text-sm font-medium text-slate-300 mb-2">Message</label>
                                <textarea name="message" id="message" rows="4" required class="w-full px-4 py-3 rounded-lg bg-slate-950 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all" placeholder="How can we help you?"></textarea>
                                @error('message')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <button type="submit" class="w-full py-4 px-6 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-lg transition-all transform hover:scale-[1.02] shadow-lg shadow-amber-500/20">
                                Send Message
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endunless

    <!-- Footer -->
    <footer class="bg-slate-950/80 backdrop-blur-sm border-t border-slate-800 py-12 relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center">
            <div class="flex items-center gap-2 mb-4 md:mb-0">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $brandName }}" class="h-6 w-auto object-contain">
                    <span class="text-slate-300 font-semibold">{{ $brandName }}</span>
                @else
                    <img src="{{ asset('images/logo.png') }}" alt="Elections HQ" class="h-6 w-auto object-contain">
                    <span class="text-slate-300 font-semibold">Elections<span class="text-amber-500">HQ</span></span>
                @endif
            </div>
            <div class="text-slate-500 text-sm">
                &copy; {{ date('Y') }} {{ $brandName }}. All rights reserved.
            </div>
        </div>
    </footer>

</body>
</html>
