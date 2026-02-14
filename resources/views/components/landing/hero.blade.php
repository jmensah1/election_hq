@props(['organization', 'brandName', 'logoUrl'])

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
                <a href="{{ route('why-choose-us') }}" class="px-8 py-4 bg-slate-800/50 hover:bg-slate-800 text-white font-semibold rounded-lg border border-slate-700 backdrop-blur-sm transition-all hover:border-slate-500">
                    Why Choose Us
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
