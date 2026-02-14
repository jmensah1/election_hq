@props(['brandName', 'logoUrl'])

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
            &copy; {{ date('Y') }} {{ $brandName ?? 'Elections HQ' }}. All rights reserved.
        </div>
    </div>
</footer>
