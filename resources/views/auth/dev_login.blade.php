<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dev Login - Elections HQ</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h1 class="text-2xl font-bold mb-6 text-center text-gray-800">Dev Login Bypass</h1>
        
        <form action="{{ route('dev.login') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Simulate Email</label>
                <input type="email" name="email" id="email" required 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border"
                    placeholder="student@university.edu"
                    value="test@example.com">
            </div>

            <button type="submit" 
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Simulate Google Login
            </button>
        </form>

        @if($errors->any())
            <div class="mt-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="mt-8">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Suggested Users</h3>
            <ul class="mt-3 grid grid-cols-1 gap-2">
                @forelse($suggestedEmails as $user)
                    <li>
                        <button onclick="document.getElementById('email').value = '{{ $user->allowed_email }}'" 
                            class="w-full text-left px-3 py-2 bg-gray-50 hover:bg-gray-100 rounded border border-gray-200 text-sm">
                            <div class="font-medium text-gray-900">{{ $user->name }}</div>
                            <div class="text-gray-500 text-xs">{{ $user->allowed_email }}</div>
                        </button>
                    </li>
                @empty
                    <li class="text-sm text-gray-400 italic">No active users found.</li>
                @endforelse
            </ul>
        </div>

        <div class="mt-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Pending Voters</h3>
            <ul class="mt-3 grid grid-cols-1 gap-2">
                @forelse($pendingEmails as $email)
                    <li>
                        <button onclick="document.getElementById('email').value = '{{ $email }}'" 
                            class="w-full text-left px-3 py-2 bg-yellow-50 hover:bg-yellow-100 rounded border border-yellow-200 text-sm text-yellow-800">
                            {{ $email }}
                        </button>
                    </li>
                @empty
                    <li class="text-sm text-gray-400 italic">No pending voters found.</li>
                @endforelse
            </ul>
        </div>
    </div>
</body>
</html>
