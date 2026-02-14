<!DOCTYPE html>
@php
    $appLocale = str_replace('_', '-', app()->getLocale());
@endphp
<html lang="{{ $appLocale }}" class="scroll-smooth">
<head>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-W9LKW372');</script>
    <!-- End Google Tag Manager -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Why Choose Us - Elections HQ</title>

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
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-W9LKW372"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

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
                <a href="/" class="flex-shrink-0 flex items-center gap-3">
                    <img src="{{ asset('images/logo.png') }}" alt="Elections HQ" class="h-10 w-auto object-contain">
                    <span class="font-bold text-xl tracking-tight text-white">
                        Elections<span class="text-amber-500">HQ</span>
                    </span>
                </a>

                <!-- Auth Links / Navigation -->
                <div class="flex items-center gap-6">
                    <a href="/" class="text-sm font-medium text-slate-300 hover:text-white transition-colors">Home</a>
                    <a href="/#pricing" class="text-sm font-medium text-slate-300 hover:text-white transition-colors">Pricing</a>
                    <a href="/#contact" class="text-sm font-medium text-slate-300 hover:text-white transition-colors">Contact Us</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="relative z-10 flex-grow pt-24 pb-16">
        <!-- Hero Section -->
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center mb-20">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-800/50 border border-slate-700/50 text-amber-400 text-xs font-semibold tracking-wide uppercase mb-6 backdrop-blur-sm">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                The Smart Choice
            </div>
            
            <h1 class="text-4xl md:text-6xl font-bold tracking-tight text-white mb-6 leading-tight">
                Why Instituations Choose <br/>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-200 via-amber-400 to-amber-600">Elections HQ</span>
            </h1>
            
            <p class="mt-4 max-w-2xl mx-auto text-xl text-slate-400 leading-relaxed">
                We provide a world-class voting infrastructure that combines military-grade security with an intuitive user experience.
            </p>
        </div>

        <!-- Advantages Grid -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Advantage 1 -->
                <div class="p-8 rounded-2xl bg-slate-900/50 backdrop-blur-sm border border-slate-800 hover:border-amber-500/30 transition-all hover:bg-slate-900/80 group">
                    <div class="w-12 h-12 bg-slate-900 rounded-lg flex items-center justify-center mb-6 ring-1 ring-slate-800 group-hover:ring-amber-500/50 transition-all">
                        <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Uncompromised Security</h3>
                    <p class="text-slate-400 leading-relaxed">
                        Our platform uses advanced encryption and a decoupled architecture to ensure that every vote is secure, anonymous, and verifiable. Your election integrity is our top priority.
                    </p>
                </div>

                <!-- Advantage 2 -->
                <div class="p-8 rounded-2xl bg-slate-900/50 backdrop-blur-sm border border-slate-800 hover:border-amber-500/30 transition-all hover:bg-slate-900/80 group">
                    <div class="w-12 h-12 bg-slate-900 rounded-lg flex items-center justify-center mb-6 ring-1 ring-slate-800 group-hover:ring-amber-500/50 transition-all">
                        <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Effortless Setup</h3>
                    <p class="text-slate-400 leading-relaxed">
                        Launch your election in minutes, not days. Our intuitive dashboard makes it easy to manage candidates, voters, and results without needing any technical expertise.
                    </p>
                </div>

                <!-- Advantage 3 -->
                <div class="p-8 rounded-2xl bg-slate-900/50 backdrop-blur-sm border border-slate-800 hover:border-amber-500/30 transition-all hover:bg-slate-900/80 group">
                    <div class="w-12 h-12 bg-slate-900 rounded-lg flex items-center justify-center mb-6 ring-1 ring-slate-800 group-hover:ring-amber-500/50 transition-all">
                        <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Real-Time Results</h3>
                    <p class="text-slate-400 leading-relaxed">
                        Watch the results come in live as votes are cast. Our real-time analytics provide immediate insights while maintaining strict voter anonymity.
                    </p>
                </div>

                <!-- Advantage 4 -->
                <div class="p-8 rounded-2xl bg-slate-900/50 backdrop-blur-sm border border-slate-800 hover:border-amber-500/30 transition-all hover:bg-slate-900/80 group">
                    <div class="w-12 h-12 bg-slate-900 rounded-lg flex items-center justify-center mb-6 ring-1 ring-slate-800 group-hover:ring-amber-500/50 transition-all">
                        <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Cost-Effective</h3>
                    <p class="text-slate-400 leading-relaxed">
                        We offer transparent, competitive pricing plans that scale with your organization. No hidden fees or surprise charges—just great value.
                    </p>
                </div>

                <!-- Advantage 5 -->
                <div class="p-8 rounded-2xl bg-slate-900/50 backdrop-blur-sm border border-slate-800 hover:border-amber-500/30 transition-all hover:bg-slate-900/80 group">
                    <div class="w-12 h-12 bg-slate-900 rounded-lg flex items-center justify-center mb-6 ring-1 ring-slate-800 group-hover:ring-amber-500/50 transition-all">
                        <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Audit-Grade Integrity</h3>
                    <p class="text-slate-400 leading-relaxed">
                        Every action is logged in a tamper-evident audit trail. We provide the tools you need to prove the fairness and accuracy of your election to all stakeholders.
                    </p>
                </div>

                <!-- Advantage 6 -->
                <div class="p-8 rounded-2xl bg-slate-900/50 backdrop-blur-sm border border-slate-800 hover:border-amber-500/30 transition-all hover:bg-slate-900/80 group">
                    <div class="w-12 h-12 bg-slate-900 rounded-lg flex items-center justify-center mb-6 ring-1 ring-slate-800 group-hover:ring-amber-500/50 transition-all">
                        <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Accessible Anywhere</h3>
                    <p class="text-slate-400 leading-relaxed">
                        Our cloud-based platform is accessible from any device, anywhere in the world. Whether your voters are on campus or remote, they can vote with ease.
                    </p>
                </div>
            </div>

            <!-- CTA Section -->
            <div class="mt-20 text-center">
                <h2 class="text-3xl font-bold text-white mb-6">Ready to upgrade your elections?</h2>
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="/#pricing" class="px-8 py-4 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-lg transition-all transform hover:scale-105 shadow-[0_0_40px_rgba(245,158,11,0.4)]">
                        View Pricing
                    </a>
                    <a href="/#contact" class="px-8 py-4 bg-slate-800/50 hover:bg-slate-800 text-white font-semibold rounded-lg border border-slate-700 backdrop-blur-sm transition-all hover:border-slate-500">
                        Contact Us
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-slate-950/80 backdrop-blur-sm border-t border-slate-800 py-12 relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center">
            <div class="flex items-center gap-2 mb-4 md:mb-0">
                <img src="{{ asset('images/logo.png') }}" alt="Elections HQ" class="h-6 w-auto object-contain">
                <span class="text-slate-300 font-semibold">Elections<span class="text-amber-500">HQ</span></span>
            </div>
            <div class="text-slate-500 text-sm">
                &copy; {{ date('Y') }} Elections HQ. All rights reserved.
            </div>
        </div>
    </footer>

    @include('partials.tawk-to')
</body>
</html>
