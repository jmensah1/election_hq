<div class="max-w-5xl mx-auto">
    <!-- Progress Header -->
    <div class="mb-10">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4 font-serif">{{ $election->title }}</h1>
        <div class="relative pt-1">
             <div class="flex mb-2 items-center justify-between">
                <div>
                  <span class="text-xs font-bold inline-block py-1 px-3 uppercase rounded-full text-amber-600 bg-amber-100 dark:text-amber-300 dark:bg-amber-900/30 tracking-wide">
                    Progress
                  </span>
                </div>
                <div class="text-right">
                  <span class="text-xs font-bold inline-block text-amber-600 dark:text-amber-400">
                    {{ $reviewing ? 100 : round((($currentStep) / count($positions)) * 100) }}%
                  </span>
                </div>
              </div>
              <div class="overflow-hidden h-2.5 mb-4 text-xs flex rounded-full bg-gray-200 dark:bg-slate-700">
                <div style="width:{{ $reviewing ? 100 : round((($currentStep) / count($positions)) * 100) }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-gradient-to-r from-amber-400 to-amber-600 transition-all duration-500 ease-out"></div>
              </div>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg border border-gray-100 dark:border-slate-700/50 overflow-hidden min-h-[500px] flex flex-col transition-colors duration-300">
        @if($reviewing)
            <!-- REVIEW STEP -->
            <div class="p-8 md:p-10 flex-grow">
                <div class="flex items-center gap-3 mb-8 border-b border-gray-100 dark:border-slate-700 pb-5">
                    <div class="bg-indigo-100 dark:bg-indigo-900/30 p-2 rounded-lg text-indigo-600 dark:text-indigo-400">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Review Your Choices</h2>
                </div>
                
                <div class="grid gap-4">
                    @foreach($positions as $index => $position)
                        @php
                            $candidateId = $ballot['pos_' . $position->id] ?? null;
                            $candidate = $position->candidates->where('id', $candidateId)->first();
                        @endphp
                        <div class="bg-gray-50 dark:bg-slate-700/30 rounded-xl p-5 flex flex-col sm:flex-row justify-between items-start sm:items-center border border-gray-200 dark:border-slate-700 group hover:border-indigo-300 dark:hover:border-indigo-500/50 transition-colors">
                            <div class="mb-3 sm:mb-0">
                                <h3 class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 font-semibold mb-1">{{ $position->name }}</h3>
                                <p class="text-lg font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                    @if($candidate)
                                        <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        {{ $candidate->user->name }}
                                    @else
                                        <span class="text-gray-400 italic">No Selection</span>
                                    @endif
                                </p>
                            </div>
                            <button wire:click="jumpToStep({{ $index }})" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 underline underline-offset-2 decoration-transparent hover:decoration-current transition-all">
                                Change Selection
                            </button>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8 p-5 bg-amber-50 dark:bg-amber-900/10 text-amber-800 dark:text-amber-200 rounded-xl text-sm border border-amber-200 dark:border-amber-700/30 flex items-start gap-3">
                     <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div>
                        <strong class="block mb-1 font-bold">Confidentiality Notice</strong>
                        Once you submit your vote, it is encrypted and permanently recorded. This action cannot be undone.
                    </div>
                </div>
            </div>
             <div class="bg-gray-50 dark:bg-slate-900/50 px-8 py-6 border-t border-gray-200 dark:border-slate-800 flex justify-between items-center backdrop-blur-sm">
                <button wire:click="$set('reviewing', false)" class="px-6 py-2.5 border border-gray-300 dark:border-slate-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-slate-700 font-medium transition-colors">
                    Back to Ballot
                </button>
                <button wire:click="submitVote" wire:loading.attr="disabled" class="px-8 py-3 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-500 hover:to-green-600 text-white rounded-lg font-bold text-lg shadow-lg hover:shadow-green-500/20 transform hover:-translate-y-0.5 transition-all flex items-center gap-2">
                    <span wire:loading.remove wire:target="submitVote">Confirm & Vote</span>
                    <span wire:loading wire:target="submitVote" class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Processing...
                    </span>
                </button>
            </div>

        @else
            <!-- VOTING STEP -->
            @php
                $currentPosition = $positions[$currentStep];
            @endphp
            
            <div class="p-8 md:p-10 flex-grow">
                 <div class="mb-8">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <span class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 dark:bg-slate-700 text-sm text-gray-500 dark:text-gray-400 font-mono">{{ $currentStep + 1 }}</span>
                        {{ $currentPosition->name }}
                    </h2>
                    @if($currentPosition->description)
                        <p class="text-gray-500 dark:text-gray-400 mt-2 ml-11 text-lg leading-relaxed">{{ $currentPosition->description }}</p>
                    @endif
                    <div class="mt-4 ml-11 inline-flex items-center px-3 py-1 rounded-md bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 text-sm font-medium border border-blue-100 dark:border-blue-800">
                        Select {{ $currentPosition->max_votes }} candidate{{ $currentPosition->max_votes > 1 ? 's' : '' }}
                    </div>
                </div>

                @error("ballot.pos_{$currentPosition->id}")
                    <div class="mb-8 ml-11 p-4 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 rounded-lg border border-red-200 dark:border-red-800/50 flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        {{ $message }}
                    </div>
                @enderror

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 ml-0 md:ml-11">
                    @foreach($currentPosition->candidates as $candidate)
                        <label wire:key="candidate-{{ $candidate->id }}" class="cursor-pointer group relative">
                            <input type="radio" 
                                name="position_{{ $currentPosition->id }}" 
                                wire:model.live="ballot.pos_{{ $currentPosition->id }}" 
                                value="{{ $candidate->id }}" 
                                class="peer sr-only">
                            
                            <!-- Card -->
                            <div class="h-full bg-white dark:bg-slate-700/50 rounded-xl border-2 p-6 transition-all duration-200 
                                        peer-checked:border-amber-500 peer-checked:bg-amber-50/50 dark:peer-checked:bg-amber-900/10 peer-checked:shadow-lg peer-checked:shadow-amber-500/10
                                        hover:border-gray-300 dark:hover:border-slate-500 hover:shadow-md border-gray-200 dark:border-slate-700 flex flex-col items-center text-center">
                                
                                <div class="relative mb-6">
                                     @if($candidate->photo_path)
                                        <img src="{{ Storage::url($candidate->photo_path) }}" class="h-40 w-40 rounded-full object-cover border-4 border-white dark:border-slate-600 shadow-md peer-checked:border-amber-500 transition-all transform group-hover:scale-105">
                                    @else
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($candidate->user->name) }}&background=random&size=160" class="h-40 w-40 rounded-full border-4 border-white dark:border-slate-600 shadow-md transition-all transform group-hover:scale-105">
                                    @endif
                                    
                                    <!-- Checkmark -->
                                    <div class="absolute -bottom-2 -right-2 bg-amber-500 text-white rounded-full p-2 opacity-0 peer-checked:opacity-100 transform scale-0 peer-checked:scale-100 transition-all duration-300 shadow-lg ring-2 ring-white dark:ring-slate-800">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </div>
                                
                                <h3 class="font-bold text-gray-900 dark:text-white text-xl mb-1 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">{{ $candidate->user->name }}</h3>
                                <!-- Can add candidate number or party here if available -->
                                
                                {{-- Manifesto Snippet --}}
                                </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="bg-gray-50 dark:bg-slate-900/50 px-8 py-6 border-t border-gray-200 dark:border-slate-800 flex justify-between items-center backdrop-blur-sm">
                <button wire:click="previousStep" 
                        @if($currentStep === 0) disabled class="opacity-50 cursor-not-allowed px-6 py-2.5 border border-gray-200 dark:border-slate-700 rounded-lg text-gray-400 dark:text-gray-600 font-medium" 
                        @else class="px-6 py-2.5 border border-gray-300 dark:border-slate-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-slate-700 font-medium transition-colors" @endif>
                    Previous
                </button>

                <div class="hidden sm:block text-center text-sm font-medium text-gray-500 dark:text-gray-400">
                    Step {{ $currentStep + 1 }} of {{ count($positions) }}
                </div>

                <button wire:click="nextStep" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg font-bold shadow-md shadow-indigo-500/20 hover:shadow-lg transition-all transform hover:-translate-y-0.5 flex items-center gap-2">
                    <span>{{ $currentStep === count($positions) - 1 ? 'Review Vote' : 'Next Position' }}</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                </button>
            </div>
        @endif
    </div>
</div>
