<div class="max-w-4xl mx-auto">
    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Self Nomination Portal</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">Nominate yourself as a candidate for an open election.</p>
    </div>

    {{-- Not a Voter --}}
    @if(!$isVoter)
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 p-12 text-center">
            <div class="mx-auto h-16 w-16 text-red-400 dark:text-red-500 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Not Authorized</h3>
            <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                You must be a registered voter in this organization to nominate yourself. Please contact the election commission if you believe this is an error.
            </p>
        </div>

    {{-- No Open Elections --}}
    @elseif($elections->isEmpty())
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 p-12 text-center">
            <div class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-500 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No Open Nominations</h3>
            <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                There are currently no elections accepting self-nominations. Please check back later or contact the election commission for more information.
            </p>
        </div>

    {{-- Already Nominated --}}
    @elseif($existingNomination)
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                <h2 class="text-lg font-semibold text-white">Your Existing Nomination</h2>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-slate-700">
                <div class="flex justify-between items-center px-6 py-4">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Election</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $existingNomination->election->title }}</span>
                </div>
                <div class="flex justify-between items-center px-6 py-4">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Position</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $existingNomination->position->name }}</span>
                </div>
                <div class="flex justify-between items-center px-6 py-4">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</span>
                    @php
                        $statusColors = [
                            'pending_submission' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-400',
                            'pending_vetting' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400',
                            'approved' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400',
                            'rejected' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400',
                        ];
                        $color = $statusColors[$existingNomination->nomination_status] ?? 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300';
                    @endphp
                    <span class="px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider {{ $color }}">
                        {{ str_replace('_', ' ', $existingNomination->nomination_status) }}
                    </span>
                </div>
            </div>
        </div>

        @if($submitted)
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 p-12 text-center">
                <div class="mx-auto h-20 w-20 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center mb-6">
                    <svg class="h-10 w-10 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Nomination Submitted!</h3>
                <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                    Your nomination has been submitted and is now pending review by the Electoral Commission. You will be notified once a decision has been made.
                </p>
            </div>
        @else
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/50 rounded-xl p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-amber-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 6a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 6zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-amber-800 dark:text-amber-300">
                            You have already nominated yourself for a position in this election. Each candidate may only nominate for <strong>one position</strong> per election.
                        </p>
                    </div>
                </div>
            </div>
        @endif

    {{-- Nomination Form --}}
    @else
        <form wire:submit="submit" class="space-y-6">
            {{-- Election Selection --}}
            @if($elections->count() > 1)
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Select Election</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Choose the election you wish to participate in.</p>
                    </div>
                    <div class="p-6">
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach($elections as $election)
                                <label class="relative flex cursor-pointer rounded-xl border p-4 transition-all
                                    {{ $selectedElectionId == $election->id 
                                        ? 'border-indigo-500 dark:border-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 ring-2 ring-indigo-500' 
                                        : 'border-gray-200 dark:border-slate-600 hover:border-indigo-300 dark:hover:border-indigo-600' }}">
                                    <input type="radio" wire:model.live="selectedElectionId" value="{{ $election->id }}" class="sr-only">
                                    <div class="flex flex-col flex-1">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $election->title }}</span>
                                        @if($election->description)
                                            <span class="mt-1 text-xs text-gray-500 dark:text-gray-400 line-clamp-2">{{ $election->description }}</span>
                                        @endif
                                        <span class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                                            Closes: {{ $election->nomination_end_date->format('M d, Y \a\t h:i A') }}
                                        </span>
                                    </div>
                                    @if($selectedElectionId == $election->id)
                                        <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                        @error('selectedElectionId') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            @else
                {{-- Single election — show info banner --}}
                @php $election = $elections->first(); @endphp
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
                    <div class="bg-gradient-to-r from-indigo-600 to-blue-600 px-6 py-4">
                        <h2 class="text-lg font-semibold text-white">{{ $election->title }}</h2>
                        <p class="text-indigo-100 text-sm mt-1">Nominations close {{ $election->nomination_end_date->format('M d, Y \a\t h:i A') }}</p>
                    </div>
                    @if($election->description)
                        <div class="px-6 py-3 bg-indigo-50 dark:bg-indigo-900/10 border-b border-gray-100 dark:border-slate-700">
                            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $election->description }}</p>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Position Selection --}}
            @if($selectedElectionId)
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Select Position</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Choose the position you would like to contest for. You may only select one.</p>
                    </div>
                    <div class="p-6">
                        @if($positions->isEmpty())
                            <p class="text-center text-gray-500 dark:text-gray-400 py-4">No positions are currently available for this election.</p>
                        @else
                            <div class="grid gap-3 sm:grid-cols-2">
                                @foreach($positions as $position)
                                    <label class="relative flex cursor-pointer rounded-xl border p-4 transition-all
                                        {{ $selectedPositionId == $position->id 
                                            ? 'border-indigo-500 dark:border-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 ring-2 ring-indigo-500' 
                                            : 'border-gray-200 dark:border-slate-600 hover:border-indigo-300 dark:hover:border-indigo-600' }}">
                                        <input type="radio" wire:model.live="selectedPositionId" value="{{ $position->id }}" class="sr-only">
                                        <div class="flex flex-col flex-1">
                                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $position->name }}</span>
                                            @if($position->description)
                                                <span class="mt-1 text-xs text-gray-500 dark:text-gray-400 line-clamp-2">{{ $position->description }}</span>
                                            @endif
                                        </div>
                                        @if($selectedPositionId == $position->id)
                                            <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                            </svg>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                        @endif
                        @error('selectedPositionId') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Application Form --}}
                @if($selectedPositionId)
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Complete Your Application</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Fill in the details below to submit your nomination.</p>
                        </div>

                        <div class="p-6 space-y-6">
                            {{-- Name (read-only) --}}
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Full Name
                                </label>
                                <input 
                                    type="text" 
                                    id="name" 
                                    wire:model="name" 
                                    class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-shadow"
                                    placeholder="Enter your full name"
                                >
                                @error('name') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            </div>

                            {{-- Photo Upload --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Candidate Photo</label>
                                <div class="flex items-center gap-6">
                                    <div class="flex-shrink-0 relative">
                                        {{-- Loading overlay --}}
                                        <div wire:loading wire:target="photo" class="absolute inset-0 h-32 w-32 rounded-xl bg-white/80 dark:bg-slate-800/80 flex items-center justify-center z-10">
                                            <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </div>

                                        @if ($photo)
                                            <img src="{{ $photo->temporaryUrl() }}" class="h-32 w-32 object-cover rounded-xl ring-2 ring-indigo-500 ring-offset-2 dark:ring-offset-slate-800">
                                        @else
                                            <div class="h-32 w-32 rounded-xl bg-gray-100 dark:bg-slate-700 flex items-center justify-center">
                                                <svg class="h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <label for="self-nom-photo" class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 dark:border-slate-600 rounded-xl cursor-pointer hover:border-indigo-500 dark:hover:border-indigo-400 transition-colors bg-gray-50 dark:bg-slate-900/50">
                                            <div wire:loading.remove wire:target="photo" class="flex flex-col items-center justify-center pt-5 pb-6">
                                                <svg class="w-8 h-8 mb-2 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                                </svg>
                                                <p class="text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold text-indigo-600 dark:text-indigo-400">Click to upload</span> or drag and drop</p>
                                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">PNG, JPG up to 2MB</p>
                                            </div>
                                            <div wire:loading wire:target="photo" class="flex flex-col items-center justify-center pt-5 pb-6">
                                                <svg class="animate-spin h-8 w-8 mb-2 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Uploading photo...</p>
                                            </div>
                                            <input type="file" wire:model="photo" id="self-nom-photo" class="hidden" accept="image/*">
                                        </label>
                                    </div>
                                </div>
                                @error('photo') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            </div>

                            {{-- Manifesto --}}
                            <div>
                                <label for="self-nom-manifesto" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Manifesto / Campaign Statement
                                </label>
                                <textarea 
                                    id="self-nom-manifesto" 
                                    wire:model="manifesto" 
                                    rows="8" 
                                    class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-shadow resize-none"
                                    placeholder="Share your vision, goals, and why voters should support you..."
                                ></textarea>
                                @error('manifesto') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            </div>

                            {{-- Terms --}}
                            <div class="flex items-start gap-3 p-4 rounded-xl bg-gray-50 dark:bg-slate-900/50 border border-gray-200 dark:border-slate-700">
                                <div class="flex items-center h-5 mt-0.5">
                                    <input 
                                        id="self-nom-terms" 
                                        wire:model="terms_accepted" 
                                        type="checkbox" 
                                        class="w-5 h-5 text-indigo-600 border-gray-300 dark:border-slate-600 rounded focus:ring-indigo-500 dark:bg-slate-800"
                                    >
                                </div>
                                <label for="self-nom-terms" class="text-sm text-gray-700 dark:text-gray-300">
                                    I agree to abide by the <span class="font-semibold">election rules and regulations</span>. I confirm that all information provided is accurate and truthful.
                                </label>
                            </div>
                            @error('terms_accepted') <p class="-mt-4 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror

                            {{-- Submit Button --}}
                            <div class="flex justify-end pt-4">
                                <button 
                                    type="submit" 
                                    class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-semibold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-slate-800 shadow-lg shadow-indigo-500/25 transition-all transform hover:-translate-y-0.5"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="opacity-50 cursor-wait"
                                >
                                    <span wire:loading.remove wire:target="submit">
                                        <svg class="w-5 h-5 mr-2 -ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </span>
                                    <span wire:loading wire:target="submit">
                                        <svg class="animate-spin w-5 h-5 mr-2 -ml-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                    Submit Nomination
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </form>
    @endif
</div>
