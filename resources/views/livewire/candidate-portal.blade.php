<div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                Candidate Nomination Portal
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                Complete your profile to run in the election.
            </p>
        </div>
        
        @if(!$candidate)
            <div class="px-4 py-5 sm:p-6 text-center text-gray-500">
                You do not have any active nomination invitations.
            </div>
        @else
            <div class="border-t border-gray-200">
                <dl>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Election</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $candidate->election->title }}</dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Position</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $candidate->position->name }}</dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1 text-sm font-bold sm:mt-0 sm:col-span-2 uppercase {{ $candidate->nomination_status === 'approved' ? 'text-green-600' : ($candidate->nomination_status === 'rejected' ? 'text-red-600' : 'text-yellow-600') }}">
                            {{ str_replace('_', ' ', $candidate->nomination_status) }}
                        </dd>
                    </div>
                </dl>
            </div>

            @if($candidate->nomination_status === 'pending_submission')
                <form wire:submit="submit" class="border-t border-gray-200 px-4 py-5 sm:px-6 space-y-6">
                    <div>
                        <label for="manifesto" class="block text-sm font-medium text-gray-700">Manifesto / Campaign Statement</label>
                        <div class="mt-1">
                            <textarea id="manifesto" wire:model="manifesto" rows="10" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                        </div>
                        @error('manifesto') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="photo" class="block text-sm font-medium text-gray-700">Candidate Photo</label>
                        <div class="mt-1 flex items-center">
                            @if ($photo)
                                <img src="{{ $photo->temporaryUrl() }}" class="h-32 w-32 object-cover rounded-lg mr-4">
                            @endif
                            <input type="file" wire:model="photo" id="photo" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>
                        @error('photo') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="terms" wire:model="terms_accepted" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="terms" class="font-medium text-gray-700">I agree to the election rules & regulations.</label>
                            @error('terms_accepted') <p class="block mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Submit Nomination
                        </button>
                    </div>
                </form>
            @elseif($candidate->nomination_status === 'pending_vetting')
                <div class="px-4 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Application Submitted</h3>
                    <p class="mt-1 text-sm text-gray-500">Your nomination is currently being vetted by the Electoral Commission.</p>
                </div>
            @endif
        @endif
    </div>
</div>
