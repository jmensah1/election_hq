<x-voter-layout>
    <div class="min-h-[60vh] flex flex-col justify-center items-center text-center">
         <div class="mb-8 p-6 bg-green-100 rounded-full animate-bounce">
            <svg class="h-16 w-16 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Vote Submitted Successfully!</h1>
        <p class="text-xl text-gray-600 max-w-lg mx-auto mb-8">
            Thank you for participating. Your vote has been securely recorded and anonymized.
        </p>
        
        <div class="flex gap-4">
             <a href="{{ route('voter.elections.index') }}" class="px-6 py-3 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 font-medium transition">
                Back to Elections
            </a>
            
             <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium transition">
                    Logout
                </button>
            </form>
        </div>
    </div>
</x-voter-layout>
