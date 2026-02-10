<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Set Up Your Organization - Elections HQ</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="h-full">
    <div class="min-h-full py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-xl">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold tracking-tight text-white">Set Up Your Organization</h2>
                <p class="mt-2 text-sm text-slate-400">Fill out the form below to create your organization and admin account.</p>
            </div>

            <div class="bg-slate-900 py-8 px-6 shadow sm:rounded-lg border border-slate-800">
                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-lg bg-red-900/30 border border-red-700/50 text-red-400 text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('onboarding.setup.store', ['payment' => $payment->id]) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- Organization Section -->
                    <div class="border-b border-slate-700 pb-6">
                        <h3 class="text-sm font-semibold text-amber-500 uppercase tracking-wider mb-4">Organization Details</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="organization_name" class="block text-sm font-medium text-slate-300">Organization Name</label>
                                <input type="text" name="organization_name" id="organization_name" 
                                       value="{{ old('organization_name', $lead->organization_name) }}"
                                       class="mt-1 block w-full rounded-md border border-slate-700 bg-slate-800 text-white px-3 py-2 placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                                       required>
                            </div>

                            <div>
                                <label for="subdomain" class="block text-sm font-medium text-slate-300">Subdomain</label>
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <input type="text" name="subdomain" id="subdomain" 
                                           value="{{ old('subdomain', Str::slug($lead->organization_name)) }}"
                                           class="block w-full rounded-l-md border border-slate-700 bg-slate-800 text-white px-3 py-2 placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                                           placeholder="your-organization"
                                           pattern="[a-z0-9\-]+"
                                           oninput="updatePreview()"
                                           required>
                                    <span class="inline-flex items-center rounded-r-md border border-l-0 border-slate-700 bg-slate-700 px-3 text-slate-400 text-sm">.{{ $baseDomain }}</span>
                                </div>
                                <p id="url-preview" class="mt-2 text-sm text-slate-400">
                                    Your login URL: <span class="text-amber-400 font-mono" id="preview-url">{{ Str::slug($lead->organization_name) }}.{{ $baseDomain }}/admin/login</span>
                                </p>
                            </div>

                            <div>
                                <label for="custom_domain" class="block text-sm font-medium text-slate-300">Custom Domain</label>
                                <input type="text" id="custom_domain" 
                                       value="Not available - Contact support for custom domains"
                                       class="mt-1 block w-full rounded-md border border-slate-700 bg-slate-800/50 text-slate-500 px-3 py-2 cursor-not-allowed"
                                       disabled readonly>
                                <p class="mt-1 text-xs text-slate-500">Contact our team to set up a custom domain for your organization.</p>
                            </div>

                            <div>
                                <label for="timezone" class="block text-sm font-medium text-slate-300">Timezone</label>
                                <select name="timezone" id="timezone" 
                                        class="mt-1 block w-full rounded-md border border-slate-700 bg-slate-800 text-white px-3 py-2 focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                                        required>
                                    @foreach ($timezones as $tz => $label)
                                        <option value="{{ $tz }}" {{ $tz === 'Africa/Accra' ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="logo" class="block text-sm font-medium text-slate-300">Organization Logo (Optional)</label>
                                <input type="file" name="logo" id="logo" accept="image/*"
                                       class="mt-1 block w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-slate-700 file:text-white hover:file:bg-slate-600">
                            </div>
                        </div>
                    </div>

                    <!-- Admin Account Section -->
                    <div>
                        <h3 class="text-sm font-semibold text-amber-500 uppercase tracking-wider mb-4">Admin Account</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="admin_name" class="block text-sm font-medium text-slate-300">Your Name</label>
                                <input type="text" name="admin_name" id="admin_name" 
                                       value="{{ old('admin_name', $lead->name) }}"
                                       class="mt-1 block w-full rounded-md border border-slate-700 bg-slate-800 text-white px-3 py-2 placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                                       required>
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-slate-300">Email Address</label>
                                <input type="email" id="email" 
                                       value="{{ $lead->email }}"
                                       class="mt-1 block w-full rounded-md border border-slate-700 bg-slate-800/50 text-slate-400 px-3 py-2 cursor-not-allowed"
                                       disabled readonly>
                                <p class="mt-1 text-xs text-slate-500">This is the email you'll use to log in.</p>
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium text-slate-300">Password</label>
                                <input type="password" name="password" id="password" 
                                       class="mt-1 block w-full rounded-md border border-slate-700 bg-slate-800 text-white px-3 py-2 placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                                       minlength="8"
                                       required>
                                <p class="mt-1 text-xs text-slate-500">Minimum 8 characters.</p>
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-slate-300">Confirm Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" 
                                       class="mt-1 block w-full rounded-md border border-slate-700 bg-slate-800 text-white px-3 py-2 placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                                       minlength="8"
                                       required>
                            </div>
                        </div>

                        <!-- Voting Option -->
                        <div class="mt-6 pt-6 border-t border-slate-700">
                            <div class="flex items-start">
                                <div class="flex h-5 items-center">
                                    <input id="will_vote" name="will_vote" type="checkbox" value="1" 
                                           class="h-4 w-4 rounded border-slate-700 bg-slate-800 text-amber-500 focus:ring-amber-500"
                                           onchange="toggleVoterId()">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="will_vote" class="font-medium text-slate-300">I will be voting in elections</label>
                                    <p class="text-slate-500">Check this if you want to cast votes as an admin.</p>
                                </div>
                            </div>

                            <div id="voter_id_container" class="mt-4 hidden">
                                <label for="voter_id" class="block text-sm font-medium text-slate-300">Voter ID</label>
                                <input type="text" name="voter_id" id="voter_id" 
                                       class="mt-1 block w-full rounded-md border border-slate-700 bg-slate-800 text-white px-3 py-2 placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                                       placeholder="e.g. EMP-001">
                                <p class="mt-1 text-xs text-slate-500">Unique identifier for voting (e.g., Staff ID, Membership No).</p>
                            </div>
                        </div>
                    </div>

                    <script>
                        function toggleVoterId() {
                            const checkbox = document.getElementById('will_vote');
                            const container = document.getElementById('voter_id_container');
                            const input = document.getElementById('voter_id');
                            
                            if (checkbox.checked) {
                                container.classList.remove('hidden');
                                input.required = true;
                            } else {
                                container.classList.add('hidden');
                                input.required = false;
                                input.value = '';
                            }
                        }
                    </script>

                    <div class="pt-4">
                        <button type="submit" 
                                class="w-full rounded-md bg-amber-500 py-3 px-4 text-sm font-semibold text-slate-950 shadow-sm hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 focus:ring-offset-slate-900 transition-colors">
                            Create Organization
                        </button>
                    </div>
                </form>

                <div class="mt-6 pt-6 border-t border-slate-700 text-center">
                    <form action="{{ route('onboarding.setup.skip', ['payment' => $payment->id]) }}" method="POST">
                        @csrf
                        <button type="submit" class="text-sm text-slate-400 hover:text-white transition-colors">
                            This is too technical, let the team help me instead →
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updatePreview() {
            const subdomain = document.getElementById('subdomain').value.toLowerCase().replace(/[^a-z0-9\-]/g, '');
            const baseDomain = '{{ $baseDomain }}';
            document.getElementById('preview-url').textContent = subdomain + '.' + baseDomain + '/admin/login';
        }
    </script>
</body>
</html>
