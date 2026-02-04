<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Election Selector --}}
        <x-filament::section>
            <x-slot name="heading">
                Select Election
            </x-slot>
            {{ $this->form }}
        </x-filament::section>

        @if($election)
            {{-- Header with Election Title and Status --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-4 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700">
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $election->title }}
                        </h2>
                        <x-filament::badge :color="$this->getStatusColor()">
                            {{ ucfirst($election->status) }}
                        </x-filament::badge>
                    </div>
                    @if($election->organization)
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            {{ $election->organization->name }}
                        </p>
                    @endif
                </div>
            </div>

            {{-- Stats Grid --}}
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach($this->getStats() as $stat)
                    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg bg-{{ $stat['color'] }}-100 dark:bg-{{ $stat['color'] }}-900/30">
                                <x-dynamic-component :component="$stat['icon']" class="w-5 h-5 text-{{ $stat['color'] }}-600 dark:text-{{ $stat['color'] }}-400" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stat['value'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $stat['label'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Timeline --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-calendar class="w-5 h-5" />
                        Election Timeline
                    </div>
                </x-slot>

                <div class="relative">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        @foreach($this->getTimeline() as $index => $phase)
                            <div class="flex-1 relative">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center
                                        @if($phase['active']) bg-amber-500 text-white
                                        @elseif($phase['completed']) bg-green-500 text-white
                                        @else bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400
                                        @endif">
                                        <x-dynamic-component :component="$phase['icon']" class="w-5 h-5" />
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                            {{ $phase['phase'] }}
                                            @if($phase['active'])
                                                <span class="inline-flex h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
                                            @endif
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $phase['start']->format('M d') }} - {{ $phase['end']->format('M d, Y') }}
                                        </p>
                                    </div>
                                </div>
                                @if($index < count($this->getTimeline()) - 1)
                                    <div class="hidden md:block absolute top-5 left-14 w-full h-0.5
                                        @if($phase['completed']) bg-green-500
                                        @else bg-gray-200 dark:bg-gray-700
                                        @endif">
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </x-filament::section>

            {{-- Voting Activity Chart --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-chart-bar class="w-5 h-5" />
                        Voting Activity (Last 24 Hours)
                    </div>
                </x-slot>

                @php $activity = $this->getVotingActivity(); @endphp
                <div class="h-64">
                    <canvas id="votingActivityChart"></canvas>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const ctx = document.getElementById('votingActivityChart');
                        if (ctx && typeof Chart !== 'undefined') {
                            new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels: @json($activity['labels']),
                                    datasets: [{
                                        label: 'Votes',
                                        data: @json($activity['data']),
                                        backgroundColor: 'rgba(99, 102, 241, 0.5)',
                                        borderColor: 'rgb(99, 102, 241)',
                                        borderWidth: 1
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            ticks: {
                                                stepSize: 1
                                            }
                                        }
                                    },
                                    plugins: {
                                        legend: {
                                            display: false
                                        }
                                    }
                                }
                            });
                        }
                    });
                </script>
            </x-filament::section>

            {{-- Position Results --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center justify-between w-full">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-trophy class="w-5 h-5" />
                            Results by Position
                        </div>
                        @if($election->status === 'completed' || $election->results_published)
                            <div class="flex gap-2">
                                <x-filament::button
                                    tag="a"
                                    href="{{ route('admin.elections.print', ['election' => $election->id]) }}"
                                    target="_blank"
                                    size="sm"
                                    color="gray"
                                    icon="heroicon-o-printer"
                                >
                                    Print as PDF
                                </x-filament::button>

                                <x-filament::button
                                    wire:click="exportResults"
                                    size="sm"
                                    color="gray"
                                    icon="heroicon-o-arrow-down-tray"
                                >
                                    Export Results
                                </x-filament::button>
                            </div>
                        @endif
                    </div>
                </x-slot>

                <div class="space-y-6">
                    @forelse($this->getPositionResults() as $position)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                            <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                                <h4 class="font-semibold text-gray-900 dark:text-white flex items-center justify-between">
                                    {{ $position['name'] }}
                                    @if($position['totalVotes'] > 0)
                                        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                                            {{ number_format($position['totalVotes']) }} votes
                                        </span>
                                    @endif
                                </h4>
                            </div>
                            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($position['candidates'] as $candidate)
                                    <div class="flex items-center gap-4 p-4 {{ $candidate['isWinner'] ? 'bg-green-50 dark:bg-green-900/20' : '' }}">
                                        <div class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full 
                                            {{ $candidate['rank'] === 1 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}
                                            font-bold text-sm">
                                            {{ $candidate['rank'] }}
                                        </div>
                                        @if($candidate['photo'])
                                            <img src="{{ $candidate['photo'] }}" alt="{{ $candidate['name'] }}" class="w-10 h-10 flex-shrink-0 rounded-full object-cover" style="width: 40px; height: 40px; min-width: 40px; max-width: 40px;">
                                        @else
                                            <div class="w-10 h-10 flex-shrink-0 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center" style="width: 40px; height: 40px; min-width: 40px;">
                                                <x-heroicon-o-user class="w-5 h-5 text-gray-400" />
                                            </div>
                                        @endif
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-gray-900 dark:text-white flex items-center gap-2">
                                                {{ $candidate['name'] }}
                                                @if($candidate['isWinner'])
                                                    <x-heroicon-s-trophy class="w-5 h-5 text-amber-500" />
                                                @endif
                                            </p>
                                            @if($candidate['votes'] !== null)
                                                <div class="mt-1 flex items-center gap-2">
                                                    <div class="flex-1 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                                        <div class="h-full bg-indigo-500 rounded-full transition-all duration-500"
                                                             style="width: {{ $candidate['percentage'] }}%"></div>
                                                    </div>
                                                    <span class="text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                                        {{ number_format($candidate['votes']) }} ({{ $candidate['percentage'] }}%)
                                                    </span>
                                                </div>
                                            @else
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    Results hidden until voting ends
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                                        No approved candidates
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <x-heroicon-o-inbox class="w-12 h-12 mx-auto mb-2 opacity-50" />
                            <p>No positions found for this election</p>
                        </div>
                    @endforelse
                </div>
            </x-filament::section>
        @else
            <x-filament::section>
                <div class="text-center py-12">
                    <x-heroicon-o-document-magnifying-glass class="w-16 h-16 mx-auto text-gray-400 mb-4" />
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No Election Selected</h3>
                    <p class="text-gray-500 dark:text-gray-400">Select an election from the dropdown above to view its dashboard.</p>
                </div>
            </x-filament::section>
        @endif
    </div>

    {{-- Include Chart.js from CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</x-filament-panels::page>
