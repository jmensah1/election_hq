<x-voter-layout>
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">Published Results</h1>

        @if($elections->isEmpty())
             <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-8 text-center border border-gray-100 dark:border-slate-700">
                <div class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500 mb-4">
                     <x-heroicon-o-chart-bar class="w-12 h-12" />
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">No published results</h3>
                <p class="mt-2 text-gray-500 dark:text-gray-400">Results have not been published for any elections yet.</p>
             </div>
        @else
            <div class="grid gap-6 md:grid-cols-1">
                @foreach($elections as $election)
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm overflow-hidden hover:shadow-md transition duration-300 border border-gray-100 dark:border-slate-700">
                        <div class="p-6 sm:p-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                            <div>
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400 rounded-full text-xs font-semibold tracking-wider uppercase">
                                        {{ $election->status === 'completed' ? 'Completed' : 'Results Published' }}
                                    </span>
                                    <span class="text-xs text-gray-400 dark:text-gray-500">
                                        Ended {{ $election->voting_end_date->format('M d, Y') }}
                                    </span>
                                </div>
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ $election->title }}</h2>
                                <p class="text-gray-600 dark:text-gray-300 line-clamp-2 max-w-xl">{{ $election->description }}</p>
                            </div>
                            
                            <div class="flex-shrink-0">
                                <a href="{{ route('voter.results', $election) }}" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-indigo-700 bg-indigo-100 hover:bg-indigo-200 dark:bg-indigo-900/50 dark:text-indigo-300 dark:hover:bg-indigo-900 shadow-sm transition transform hover:-translate-y-0.5">
                                    View Full Results &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-voter-layout>
