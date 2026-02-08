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

        Log::info('Voter Login Attempt', [
            'email' => $email,
            'organization_id' => $organization->id,
            'organization_name' => $organization->name,
        ]);

        // 1. Check the Guest List (The Guard) - Case insensitive email comparison
        $membership = OrganizationUser::where('organization_id', $organization->id)
            ->whereRaw('LOWER(allowed_email) = ?', [strtolower($email)])
            ->first();

        // Use strict check - user MUST be on the list OR have a candidate invitation
        if (!$membership) {
            // Check for Candidate Invitation (case insensitive)
            $hasInvitation = Candidate::where('organization_id', $organization->id)
                ->whereRaw('LOWER(email) = ?', [strtolower($email)])
                ->exists();

            if ($hasInvitation) {
                 // Create membership on the fly for invited candidate
                 $membership = OrganizationUser::create([
                     'organization_id' => $organization->id,
                     'allowed_email' => strtolower($email), // Store normalized
                     'role' => 'voter', 
                     'status' => 'active',
                     'voter_id' => 'CAND-' . strtoupper(\Illuminate\Support\Str::random(6)), 
                     'can_vote' => true,
                 ]);
                 
                 Log::info('Created membership for invited candidate', ['email' => $email]);
            } else {
                // Count total voters for this org for debugging
                $totalVoters = OrganizationUser::where('organization_id', $organization->id)->count();
                
                Log::warning('Guest List Lookup Failed', [
                    'organization_id' => $organization->id,
                    'email_attempted' => $email,
                    'total_voters_in_org' => $totalVoters,
                ]);
                throw new IneligibleVoterException();
            }
        }

        
        $user = User::where('email', $email)->first(); 

        if ($user) {
            $user->update([
                // 'name' => ... We DO NOT update the name for existing users. 
                // This preserves any custom name changes they made in the Candidate Portal.
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'email_verified_at' => $user->email_verified_at ?? now(), 
            ]);
        } else {
            $user = User::create([
                'email' => $email,
                'name' => \Illuminate\Support\Str::title($googleUser->getName()),
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