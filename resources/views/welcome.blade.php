<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
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
    <x-landing.navigation :organization="$organization" :brandName="$brandName" :logoUrl="$logoUrl" />

    <!-- Hero Section -->
    <x-landing.hero :organization="$organization" :brandName="$brandName" :logoUrl="$logoUrl" />

    @unless($organization)
        <!-- Features Section -->
        <x-landing.features />

        <!-- Pricing Section -->
        <x-landing.pricing />

        <!-- Contact Section -->
        <x-landing.contact />
    @endunless

    <!-- Footer -->
    <x-landing.footer :brandName="$brandName" :logoUrl="$logoUrl" />

    @include('partials.tawk-to')
</body>
</html>
