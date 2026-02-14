@props(['organization', 'brandName', 'logoUrl'])

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
                    <a href="{{ route('why-choose-us') }}" class="text-sm font-medium text-slate-300 hover:text-white transition-colors">Why Choose Us</a>
                    <a href="#pricing" class="text-sm font-medium text-slate-300 hover:text-white transition-colors">Pricing</a>
                    <a href="#contact" class="text-sm font-medium text-slate-300 hover:text-white transition-colors">Contact Us</a>
                @endif
            </div>
        </div>
    </div>
</nav>
