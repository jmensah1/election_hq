<li>
    <a href="{{ route('voter.elections.index') }}" class="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold {{ request()->routeIs('voter.elections.*') ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-400 hover:text-white hover:bg-slate-800' }}">
        <x-heroicon-o-home class="h-6 w-6 shrink-0" />
        Voter Panel
    </a>
</li>

@auth
    @if(auth()->user()->candidates()->exists())
        <li>
            <a href="{{ route('candidate.portal') }}" class="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold {{ request()->routeIs('candidate.portal') ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-400 hover:text-white hover:bg-slate-800' }}">
                <x-heroicon-o-user class="h-6 w-6 shrink-0" />
                Candidate Portal
            </a>
        </li>
    @endif
@endauth

<li>
    <a href="{{ route('voter.published_results') }}" class="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold {{ request()->routeIs('voter.published_results') ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-400 hover:text-white hover:bg-slate-800' }}">
        <x-heroicon-o-chart-bar class="h-6 w-6 shrink-0" />
        Published Results
    </a>
</li>
