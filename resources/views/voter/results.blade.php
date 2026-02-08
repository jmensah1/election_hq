<x-voter-layout>
    <div class="max-w-5xl mx-auto">
        <div class="mb-10 text-center">
            <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white">{{ $election->title }} - Results</h1>
            <p class="mt-2 text-lg text-gray-600 dark:text-gray-400">Official Results Declaration</p>
        </div>

        <div class="grid gap-8">
            @foreach($election->positions as $position)
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="bg-indigo-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white">{{ $position->name }}</h2>
                    </div>
                    
                    <div class="p-6">
                        @php
                            $candidates = $position->candidates->sortByDesc('vote_count');
                            $totalVotes = $candidates->sum('vote_count');
                            $maxVotes = $candidates->first()->vote_count ?? 0;
                        @endphp
                        
                        <div class="space-y-6">
                            @foreach($candidates as $candidate)
                                @php
                                    $percentage = $totalVotes > 0 ? ($candidate->vote_count / $totalVotes) * 100 : 0;
                                    $isWinner = $candidate->vote_count == $maxVotes && $maxVotes > 0;
                                @endphp
                                
                                <div class="relative">
                                    <div class="flex justify-between items-end mb-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-gray-800 text-lg">{{ $candidate->user->name }}</span>
                                            @if($isWinner)
                                                <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-0.5 rounded-full border border-yellow-200 font-bold">WINNER</span>
                                            @endif
                                        </div>
                                        <div class="text-right">
                                            <span class="block font-bold text-2xl text-gray-900">{{ number_format($candidate->vote_count) }}</span>
                                            <span class="text-xs text-gray-500 font-medium">{{ number_format($percentage, 1) }}%</span>
                                        </div>
                                    </div>
                                    
                                    <div class="w-full bg-gray-100 rounded-full h-4 overflow-hidden">
                                        <div class="h-4 rounded-full {{ $isWinner ? 'bg-indigo-600' : 'bg-gray-400' }} transition-all duration-1000" style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-6 pt-4 border-t border-gray-100 text-sm text-gray-500 text-right">
                            Total Votes Cast: {{ number_format($totalVotes) }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-voter-layout>
