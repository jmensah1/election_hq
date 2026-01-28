<x-filament-panels::page>
    <x-filament::section>
        {{ $this->form }}
    </x-filament::section>

    @if($election_id)
        <div class="space-y-6">
            @foreach($positions as $position)
                <x-filament::section :heading="$position->name">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($candidates[$position->id] ?? [] as $candidate)
                            <div class="p-4 border rounded-lg flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    @if($candidate->photo_path)
                                        <img src="{{ Storage::url($candidate->photo_path) }}" class="w-12 h-12 rounded-full object-cover">
                                    @else
                                        <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center">
                                            <span class="text-gray-500 text-xl">{{ substr($candidate->user->name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                    <div>
                                        <h3 class="font-bold">{{ $candidate->user->name }}</h3>
                                        <p class="text-sm text-gray-500">Candidate</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-2xl font-bold text-primary-600">
                                        {{ $results[$candidate->id] ?? 0 }}
                                    </span>
                                    <span class="text-sm text-gray-500 block">votes</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-filament::section>
            @endforeach
        </div>
    @else
        <div class="p-8 text-center text-gray-500">
            Please select an election to view results.
        </div>
    @endif
</x-filament-panels::page>
