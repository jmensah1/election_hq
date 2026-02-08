<div class="max-w-4xl mx-auto">
    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Candidate Nomination Portal</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">Complete your profile to run in the election.</p>
    </div>

    @if(!$candidate)
        {{-- No Invitation State --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 p-12 text-center">
            <div class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-500 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 9v.906a2.25 2.25 0 01-1.183 1.981l-6.478 3.488M2.25 9v.906a2.25 2.25 0 001.183 1.981l6.478 3.488m8.839 2.51l-4.66-2.51m0 0l-1.023-.55a2.25 2.25 0 00-2.134 0l-1.022.55m0 0l-4.661 2.51m16.5-1.556a.75.75 0 01-1.088.79l-7.5-4.039a.75.75 0 01-.912 0l-7.5 4.039a.75.75 0 01-1.088-.79V4.5A2.25 2.25 0 014.5 2.25h15A2.25 2.25 0 0121.75 4.5v13.5z" />
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No Active Nominations</h3>
            <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                You do not have any active nomination invitations. If you believe this is an error, please contact the election commission.
            </p>
        </div>
    @else
        {{-- Nomination Info Card --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-blue-600 to-blue-600 px-6 py-4">
                <h2 class="text-lg font-semibold text-white">Your Nomination Details</h2>
            </div>
            
            <div class="divide-y divide-gray-100 dark:divide-slate-700">
                <div class="flex justify-between items-center px-6 py-4">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Election</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $candidate->election->title }}</span>
                </div>
                <div class="flex justify-between items-center px-6 py-4">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Position</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $candidate->position->name }}</span>
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
                        $color = $statusColors[$candidate->nomination_status] ?? 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300';
                    @endphp
                    <span class="px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider {{ $color }}">
                        {{ str_replace('_', ' ', $candidate->nomination_status) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Form or Status Message --}}
        @if($candidate->nomination_status === 'pending_submission')
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Complete Your Application</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Fill in the details below to submit your nomination.</p>
                </div>
                
                <form wire:submit="submit" class="p-6 space-y-6">
                    {{-- Name --}}
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
                                @elseif($candidate->photo_path)
                                    <img src="{{ Storage::url($candidate->photo_path) }}" class="h-32 w-32 object-cover rounded-xl ring-2 ring-gray-200 dark:ring-slate-600">
                                @else
                                    <div class="h-32 w-32 rounded-xl bg-gray-100 dark:bg-slate-700 flex items-center justify-center">
                                        <svg class="h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <label for="photo" class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 dark:border-slate-600 rounded-xl cursor-pointer hover:border-indigo-500 dark:hover:border-indigo-400 transition-colors bg-gray-50 dark:bg-slate-900/50">
                                    {{-- Normal state --}}
                                    <div wire:loading.remove wire:target="photo" class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <svg class="w-8 h-8 mb-2 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                        </svg>
                                        <p class="text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold text-indigo-600 dark:text-indigo-400">Click to upload</span> or drag and drop</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">PNG, JPG up to 2MB</p>
                                    </div>
                                    {{-- Loading state --}}
                                    <div wire:loading wire:target="photo" class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <svg class="animate-spin h-8 w-8 mb-2 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Uploading photo...</p>
                                    </div>
                                    <input type="file" wire:model="photo" id="photo" class="hidden" accept="image/*">
                                </label>
                            </div>
                        </div>
                        @error('photo') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Manifesto --}}
                    <div>
                        <label for="manifesto" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Manifesto / Campaign Statement
                        </label>
                        <textarea 
                            id="manifesto" 
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
                                id="terms" 
                                wire:model="terms_accepted" 
                                type="checkbox" 
                                class="w-5 h-5 text-indigo-600 border-gray-300 dark:border-slate-600 rounded focus:ring-indigo-500 dark:bg-slate-800"
                            >
                        </div>
                        <label for="terms" class="text-sm text-gray-700 dark:text-gray-300">
                            I agree to abide by the <span class="font-semibold">election rules and regulations</span>. I confirm that all information provided is accurate and truthful.
                        </label>
                    </div>
                    @error('terms_accepted') <p class="-mt-4 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror

                    {{-- Submit Button --}}
                    <div class="flex justify-end pt-4">
                        <button 
                            type="submit" 
                            class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-semibold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-slate-800 shadow-lg shadow-indigo-500/25 transition-all transform hover:-translate-y-0.5"
                        >
                            <svg class="w-5 h-5 mr-2 -ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Submit Nomination
                        </button>
                    </div>
                </form>
            </div>
        @elseif($candidate->nomination_status === 'pending_vetting')
            {{-- Pending Vetting Status --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 p-12 text-center">
                <div class="mx-auto h-20 w-20 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mb-6">
                    <svg class="h-10 w-10 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Application Submitted</h3>
                <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                    Your nomination is currently being vetted by the Electoral Commission. You will be notified once a decision has been made.
                </p>
            </div>
        @elseif($candidate->nomination_status === 'approved')
            {{-- Approved Status --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 p-12 text-center">
                <div class="mx-auto h-20 w-20 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center mb-6">
                    <svg class="h-10 w-10 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Nomination Approved!</h3>
                <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                    Congratulations! Your nomination has been approved. You are now an official candidate in this election. Good luck!
                </p>
            </div>
        @elseif($candidate->nomination_status === 'rejected')
            {{-- Rejected Status --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 p-12 text-center">
                <div class="mx-auto h-20 w-20 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mb-6">
                    <svg class="h-10 w-10 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Nomination Not Approved</h3>
                <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                    Unfortunately, your nomination was not approved. Please contact the Electoral Commission for more information.
                </p>
                @if($candidate->vetting_notes)
                    <div class="mt-6 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg text-left max-w-md mx-auto">
                        <p class="text-sm font-medium text-red-800 dark:text-red-300">Reason:</p>
                        <p class="text-sm text-red-700 dark:text-red-400 mt-1">{{ $candidate->vetting_notes }}</p>
                    </div>
                @endif
            </div>
        @endif
    @endif
</div>
