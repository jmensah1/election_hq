<div class="max-w-5xl mx-auto">
    <!-- Progress Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $election->title }}</h1>
        <div class="relative pt-1">
             <div class="flex mb-2 items-center justify-between">
                <div>
                  <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-indigo-600 bg-indigo-200">
                    Progress
                  </span>
                </div>
                <div class="text-right">
                  <span class="text-xs font-semibold inline-block text-indigo-600">
                    {{ $reviewing ? 100 : round((($currentStep) / count($positions)) * 100) }}%
                  </span>
                </div>
              </div>
              <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-indigo-200">
                <div style="width:{{ $reviewing ? 100 : round((($currentStep) / count($positions)) * 100) }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-500 transition-all duration-500"></div>
              </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-xl overflow-hidden min-h-[500px] flex flex-col">
        @if($reviewing)
            <!-- REVIEW STEP -->
            <div class="p-8 flex-grow">
                <h2 class="text-3xl font-bold text-gray-900 mb-6 border-b pb-4">Review Your Choices</h2>
                
                <div class="space-y-6">
                    @foreach($positions as $index => $position)
                        @php
                            $candidateId = $ballot[$position->id] ?? null;
                            $candidate = $position->candidates->where('id', $candidateId)->first();
                        @endphp
                        <div class="bg-gray-50 rounded-lg p-4 flex justify-between items-center border border-gray-200">
                            <div>
                                <h3 class="text-sm uppercase tracking-wide text-gray-500 font-semibold">{{ $position->name }}</h3>
                                <p class="text-lg font-bold text-gray-900 mt-1">
                                    {{ $candidate ? $candidate->user->name : 'No Selection' }}
                                </p>
                            </div>
                            <button wire:click="jumpToStep({{ $index }})" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium underline">
                                Change
                            </button>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8 p-4 bg-yellow-50 text-yellow-800 rounded-lg text-sm border border-yellow-200 flex items-start gap-3">
                     <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <p>
                        <strong>Warning:</strong> Once you submit your vote, it cannot be changed. This action is final.
                    </p>
                </div>
            </div>
             <div class="bg-gray-50 px-8 py-6 border-t border-gray-200 flex justify-between items-center">
                <button wire:click="$set('reviewing', false)" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-white transition">
                    Back
                </button>
                <button wire:click="submitVote" wire:loading.attr="disabled" class="px-8 py-3 bg-green-600 text-white rounded-lg font-bold text-lg hover:bg-green-700 shadow-lg transform hover:scale-105 transition flex items-center gap-2">
                    <span wire:loading.remove wire:target="submitVote">Submit Vote</span>
                    <span wire:loading wire:target="submitVote">Submitting...</span>
                     <svg wire:loading.remove wire:target="submitVote" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </button>
            </div>

        @else
            <!-- VOTING STEP -->
            @php
                $currentPosition = $positions[$currentStep];
            @endphp
            
            <div class="p-8 flex-grow">
                 <div class="mb-6">
                    <h2 class="text-3xl font-bold text-gray-900">{{ $currentPosition->name }}</h2>
                    <p class="text-gray-500 mt-2">{{ $currentPosition->description }}</p>
                    <div class="mt-2 text-sm text-gray-400">
                        Select 1 candidate (Max votes: {{ $currentPosition->max_votes }})
                    </div>
                </div>

                @error("ballot.{$currentPosition->id}")
                    <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-lg border border-red-200 animate-pulse">
                        {{ $message }}
                    </div>
                @enderror

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($currentPosition->candidates as $candidate)
                        <label class="cursor-pointer group relative">
                            <input type="radio" 
                                   wire:model.live="ballot.{{ $currentPosition->id }}" 
                                   value="{{ $candidate->id }}" 
                                   class="peer sr-only">
                            
                            <div class="h-full bg-white rounded-xl border-2 p-6 transition-all duration-200 
                                        peer-checked:border-indigo-600 peer-checked:bg-indigo-50 peer-checked:shadow-lg
                                        hover:border-indigo-300 hover:shadow-md border-gray-200">
                                
                                <div class="flex items-center gap-4 mb-4">
                                     @if($candidate->photo_path)
                                        <img src="{{ Storage::url($candidate->photo_path) }}" class="h-16 w-16 rounded-full object-cover border-2 border-white shadow-sm">
                                    @else
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($candidate->user->name) }}&background=random" class="h-16 w-16 rounded-full">
                                    @endif
                                    
                                    <div>
                                        <h3 class="font-bold text-gray-900 text-lg group-hover:text-indigo-700 transition">{{ $candidate->user->name }}</h3>
                                    </div>
                                </div>
                                
                                <div class="prose prose-sm text-gray-600 line-clamp-4 text-xs bg-gray-50 p-3 rounded-lg border border-gray-100">
                                    {!! $candidate->manifesto ?? 'No manifesto provided.' !!}
                                </div>

                                <div class="absolute top-4 right-4 text-indigo-600 opacity-0 peer-checked:opacity-100 transition-opacity">
                                    <svg class="w-8 h-8 fill-current" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="bg-gray-50 px-8 py-6 border-t border-gray-200 flex justify-between items-center">
                <button wire:click="previousStep" 
                        @if($currentStep === 0) disabled class="opacity-50 cursor-not-allowed px-6 py-2 border border-gray-300 rounded-lg text-gray-400" @else class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-white transition" @endif>
                    Previous
                </button>

                <div class="text-center text-sm font-medium text-gray-500">
                    Step {{ $currentStep + 1 }} of {{ count($positions) }}
                </div>

                <button wire:click="nextStep" class="px-8 py-3 bg-indigo-600 text-white rounded-lg font-bold hover:bg-indigo-700 shadow-md transition transform hover:-translate-y-0.5">
                    {{ $currentStep === count($positions) - 1 ? 'Review Vote' : 'Next Position' }}
                </button>
            </div>
        @endif
    </div>
</div>
