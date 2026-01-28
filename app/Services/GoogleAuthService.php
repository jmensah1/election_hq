<?php

namespace App\Services;

use App\Models\User;
use App\Models\OrganizationUser;
use App\Exceptions\IneligibleVoterException;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class GoogleAuthService
{
    /**
     * Handle the login/registration process for a Google User.
     *
     * @param  \Laravel\Socialite\Contracts\User  $googleUser
     * @return \App\Models\User
     * @throws \App\Exceptions\IneligibleVoterException
     */
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
            $hasInvitation = \App\Models\Candidate::where('organization_id', $organization->id)
                ->where('email', $email)
                ->exists();

            if ($hasInvitation) {
                 // Create membership on the fly for invited candidate
                 $membership = OrganizationUser::create([
                     'organization_id' => $organization->id,
                     'allowed_email' => $email,
                     'role' => 'voter', // Default to voter, they are a candidate
                     'status' => 'active',
                     'voter_id' => 'CAND-' . strtoupper(\Illuminate\Support\Str::random(6)), // Temporary ID
                     'can_vote' => true,
                 ]);
                 
                 \Illuminate\Support\Facades\Log::info('Created membership for invited candidate', ['email' => $email]);
            } else {
                \Illuminate\Support\Facades\Log::warning('Guest List Lookup Failed', [
                    'organization_id' => $organization->id,
                    'email' => $email
                ]);
                throw new IneligibleVoterException();
            }
        }

        // 2. Create/Update the Global User Account
        $user = User::where('email', $email)->first(); // Use first() to avoid overwriting password for existing users

        if ($user) {
            $user->update([
                'name' => $googleUser->getName(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
            ]);
        } else {
            $user = User::create([
                'email' => $email,
                'name' => $googleUser->getName(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(32)), // Random password for DB constraint
            ]);
        }

        // 3. Link the User to the Membership (if not already linked)
        if (!$membership->user_id) {
            $membership->update([
                'user_id' => $user->id,
                'status' => 'active'
            ]);
        }

        return $user;
    }
}
