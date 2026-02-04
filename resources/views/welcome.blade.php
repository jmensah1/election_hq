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

                <!-- Auth Links -->
                <div class="flex items-center gap-4">
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
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative flex-grow flex items-center justify-center overflow-hidden pt-20">
        <!-- Background Effects -->
        <div class="absolute inset-0 z-0">
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-blue-600/20 rounded-full blur-[128px]"></div>
            <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-amber-600/10 rounded-full blur-[128px]"></div>
            <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 brightness-100 contrast-150"></div>
            <!-- Grid Pattern -->
            <div class="absolute inset-0 bg-[linear-gradient(to_right,#80808012_1px,transparent_1px),linear-gradient(to_bottom,#80808012_1px,transparent_1px)] bg-[size:24px_24px]"></div>
        </div>

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
                @unless($organization)
                    <a href="#features" class="px-8 py-4 bg-slate-800/50 hover:bg-slate-800 text-white font-semibold rounded-lg border border-slate-700 backdrop-blur-sm transition-all hover:border-slate-500">
                        Learn How it Works
                    </a>
                @endunless
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
        <div id="features" class="py-24 bg-slate-900 relative">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">Enterprise-Grade Integrity</h2>
                    <p class="text-slate-400 max-w-2xl mx-auto">
                        Built to solve the paradox of electronic voting: proving a vote was counted without revealing who cast it.
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="p-8 rounded-2xl bg-slate-950 border border-slate-800 hover:border-amber-500/30 transition-all hover:bg-slate-900/80 group">
                        <div class="w-12 h-12 bg-slate-900 rounded-lg flex items-center justify-center mb-6 group-hover:scale-110 transition-transform ring-1 ring-slate-800 group-hover:ring-amber-500/50">
                            <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">Decoupled Architecture</h3>
                        <p class="text-slate-400 leading-relaxed">
                            We separate <strong>WHO</strong> voted from <strong>WHAT</strong> they voted for into two completely isolated database tables. There is zero mathematical link between your identity and your ballot choice.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="p-8 rounded-2xl bg-slate-950 border border-slate-800 hover:border-blue-500/30 transition-all hover:bg-slate-900/80 group">
                        <div class="w-12 h-12 bg-slate-900 rounded-lg flex items-center justify-center mb-6 group-hover:scale-110 transition-transform ring-1 ring-slate-800 group-hover:ring-blue-500/50">
                            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">Immutable Audit Logs</h3>
                        <p class="text-slate-400 leading-relaxed">
                            Every system action is recorded in a tamper-evident audit trail. Admins can verify election integrity without ever compromising voter secrecy.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="p-8 rounded-2xl bg-slate-950 border border-slate-800 hover:border-green-500/30 transition-all hover:bg-slate-900/80 group">
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
    @endunless

    <!-- Footer -->
    <footer class="bg-slate-950 border-t border-slate-900 py-12">
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
