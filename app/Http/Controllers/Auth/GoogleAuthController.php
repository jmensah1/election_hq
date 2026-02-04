<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\GoogleAuthService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    protected $authService;

    public function __construct(GoogleAuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirect()
    {
        // Explicitly generate the callback URL based on the CURRENT hostname (subdomain)
        // This ensures that if we are on 'test.elections-hq.me', we tell Google to send us back there.
        $callbackUrl = route('auth.google.callback');
        
        // DEBUG: Verify the URL being generated

        
        return Socialite::driver('google')
            ->redirectUrl($callbackUrl)
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function callback(\Illuminate\Http\Request $request)
    {
        try {
            // Use stateless() to bypass InvalidStateException in local dev/http environments
            $socialiteUser = Socialite::driver('google')->stateless()->user();
            
            $user = $this->authService->handleLogin($socialiteUser);

            Auth::login($user, true);

            return redirect()->intended(route('voter.elections.index'));

        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google Login Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('login')
                ->with('error', $e->getMessage());
        }
    }
    
    /**
     * Log the user out of the application.
     */
    public function logout(Request $request) {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
}
