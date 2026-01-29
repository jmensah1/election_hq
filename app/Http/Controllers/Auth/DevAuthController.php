<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\GoogleAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Two\User as SocialiteUser;

class DevAuthController extends Controller
{
    public function show()
    {
        if (!app()->isLocal()) {
            abort(404);
        }

        // Get suggested emails from organization_user
        $organization = current_organization();
        $suggestedEmails = $organization ? $organization->users()
            ->select('organization_user.allowed_email', 'users.name')
            ->limit(10)
            ->get() : [];

        // Also get pending invitations
        $pendingEmails = $organization ? \App\Models\OrganizationUser::where('organization_id', $organization->id)
            ->whereNull('user_id')
            ->limit(5)
            ->pluck('allowed_email') : [];

        return view('auth.dev_login', compact('suggestedEmails', 'pendingEmails'));
    }

    public function login(Request $request, GoogleAuthService $authService)
    {
        if (!app()->isLocal()) {
            abort(404);
        }

        $request->validate([
            'email' => 'required|email'
        ]);

        $email = $request->input('email');

        // Mock Socialite User
        $mockUser = new SocialiteUser();
        $mockUser->map([
            'id' => 'dev_' . md5($email),
            'name' => 'Dev User (' . explode('@', $email)[0] . ')',
            'email' => $email,
            'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($email),
            'token' => 'mock_token',
        ]);

        try {
            $user = $authService->handleLogin($mockUser);
            Auth::login($user);
            return redirect()->route('voter.elections.index');
        } catch (\Exception $e) {
            return back()->withErrors(['email' => $e->getMessage()]);
        }
    }
}
