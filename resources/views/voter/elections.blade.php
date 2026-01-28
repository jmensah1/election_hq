<x-voter-layout>
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Active Elections</h1>

        @if($elections->isEmpty())
             <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                <div class="mx-auto h-12 w-12 text-gray-400 mb-4">
                     <!-- Heroicon: inbox -->
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900">No active elections</h3>
                <p class="mt-2 text-gray-500">There are currently no elections open for voting. Please check back later.</p>
             </div>
        @else
            <div class="grid gap-6 md:grid-cols-1">
                @foreach($elections as $election)
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition duration-300 border border-gray-100">
                        <div class="p-6 sm:p-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                            <div>
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold tracking-wider uppercase">
                                        Voting Open
                                    </span>
                                    <span class="text-xs text-gray-400">
                                        Ends {{ $election->voting_end_date->format('M d, Y h:i A') }}
                                    </span>
                                </div>
                                <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $election->title }}</h2>
                                <p class="text-gray-600 line-clamp-2 max-w-xl">{{ $election->description }}</p>
                            </div>
                            
                            <div class="flex-shrink-0">
                                @php
                                    $hasVoted = \App\Models\VoteConfirmation::where('election_id', $election->id)
                                        ->where('user_id', auth()->id())
                                        ->exists();
                                @endphp

                                @if($hasVoted)
                                    <div class="flex flex-col items-center">
                                         <span class="bg-gray-100 text-gray-600 px-6 py-3 rounded-lg font-medium cursor-not-allowed">
                                            Vote Cast
                                        </span>
                                        @if($election->results_published)
                                            <a href="{{ route('voter.results', $election) }}" class="mt-2 text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                                View Results &rarr;
                                            </a>
                                        @endif
                                    </div>
                                @else
                                    <a href="{{ route('voter.elections.show', $election) }}" class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition transform hover:-translate-y-0.5">
                                        Vote Now
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-voter-layout>
