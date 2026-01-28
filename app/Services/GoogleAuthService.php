<?php

namespace App\Services;

use App\Models\User;
use App\Models\OrganizationUser;
use App\Models\Candidate;
use App\Exceptions\IneligibleVoterException;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Illuminate\Support\Facades\Log;

class GoogleAuthService
{
    public function handleLogin(SocialiteUser $googleUser): User
    {
        $organization = current_organization();
        
        if (!$organization) {
            abort(404, 'Organization context not found.');
        }

        $email = $googleUser->getEmail();

        // 1. Check the Guest List (The Guard)
        $membership = OrganizationUser::where('organization_id', $organization->id)
            ->where('allowed_email', $email)
            ->first();

        // Use strict check - user MUST be on the list OR have a candidate invitation
        if (!$membership) {
            // Check for Candidate Invitation
            $hasInvitation = Candidate::where('organization_id', $organization->id)
                ->where('email', $email)
                ->exists();

            if ($hasInvitation) {
                 // Create membership on the fly for invited candidate
                 $membership = OrganizationUser::create([
                     'organization_id' => $organization->id,
                     'allowed_email' => $email,
                     'role' => 'voter', 
                     'status' => 'active',
                     'voter_id' => 'CAND-' . strtoupper(\Illuminate\Support\Str::random(6)), 
                     'can_vote' => true,
                 ]);
                 
                 Log::info('Created membership for invited candidate', ['email' => $email]);
            } else {
                Log::warning('Guest List Lookup Failed', [
                    'organization_id' => $organization->id,
                    'email' => $email
                ]);
                throw new IneligibleVoterException();
            }
        }

        
        $user = User::where('email', $email)->first(); 

        if ($user) {
            $user->update([
                'name' => $googleUser->getName(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'email_verified_at' => $user->email_verified_at ?? now(), 
            ]);
        } else {
            $user = User::create([
                'email' => $email,
                'name' => $googleUser->getName(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(32)),
                'email_verified_at' => now(), 
            ]);
        }

        if (!$membership->user_id) {
            $membership->update([
                'user_id' => $user->id,
                'status' => 'active'
            ]);
        }

        Candidate::where('email', $email)
            ->where('organization_id', $organization->id)
            ->whereNull('user_id') 
            ->update(['user_id' => $user->id]);

        return $user;
    }
}