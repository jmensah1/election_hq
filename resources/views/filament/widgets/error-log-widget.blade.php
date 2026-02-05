<x-filament::widget>
    <x-filament::section>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-danger-500" />
                    <h2 class="text-lg font-bold tracking-tight text-gray-950 dark:text-white">
                        Recent Logs
                    </h2>
                </div>
                <div>
                     @if($errorCount > 0)
                        <x-filament::badge color="danger">
                            {{ $errorCount }} Recent Errors
                        </x-filament::badge>
                     @else
                        <x-filament::badge color="success">
                            System Stable
                        </x-filament::badge>
                     @endif
                     
                     <x-filament::button size="xs" wire:click="refreshLogs" icon="heroicon-m-arrow-path" color="gray" class="ml-2">
                        Refresh
                     </x-filament::button>
                </div>
            </div>

            <div class="p-4 bg-gray-50 rounded-lg dark:bg-gray-900 overflow-x-auto font-mono text-xs">
                @forelse($recentLogs as $log)
                    <div class="whitespace-pre-wrap {{ str_contains($log, 'ERROR') || str_contains($log, 'CRITICAL') ? 'text-danger-600 dark:text-danger-400 font-bold' : 'text-gray-600 dark:text-gray-400' }}">
                        {{ $log }}
                    </div>
                @empty
                    <div class="text-gray-500 italic">No logs available.</div>
                @endforelse
            </div>
        </div>
    </x-filament::section>
</x-filament::widget>
