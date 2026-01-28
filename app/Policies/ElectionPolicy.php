<?php

namespace App\Policies;

use App\Models\Election;
use App\Models\User;
use App\Models\VoteConfirmation;
use Illuminate\Auth\Access\Response;

class ElectionPolicy
{
    /**
     * Determine whether the user can vote in the election.
     */
    public function vote(User $user, Election $election): Response
    {
        // 1. Check if user belongs to the organization (or has access via pivot)
        // Global scope usually handles organization isolation, but for safety:
        if ($user->organizations->doesntContain($election->organization_id)) {
             return Response::deny('You are not a member of this organization.');
        }

        // 2. Check if user is allowed to vote (can_vote flag in pivot)
        // Accessing pivot data: $user->organizations->find($election->organization_id)->pivot->can_vote
        $membership = $user->organizations->find($election->organization_id);
        
        if (!$membership || !$membership->pivot->can_vote) {
            return Response::deny('You are not authorized to vote in this election.');
        }

        // 3. Check Election Status
        if ($election->status !== 'voting') {
            return Response::deny('This election is not currently open for voting.');
        }

        // 4. Check Dates
        $now = now();
        if ($election->start_date && $now->lt($election->start_date)) {
            return Response::deny('Voting has not started yet.');
        }

        if ($election->end_date && $now->gt($election->end_date)) {
             return Response::deny('Voting has ended.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can view the election results.
     */
    public function viewResults(User $user, Election $election): Response
    {
        // 1. Admins and Election Officers can always view results
        // Checks logic via organization_user pivot role, simplified here assuming current org context logic holds
        $membership = $user->organizations->find($election->organization_id);
        if ($membership && in_array($membership->pivot->role, ['admin', 'election_officer', 'super_admin'])) {
             return Response::allow();
        }

        // 2. Voters can only view if results are published
        if ($election->results_published) {
            return Response::allow();
        }

        return Response::deny('Results have not been published yet.');
    }

    /**
     * Determine whether the user can manage the election.
     */
    public function manage(User $user, Election $election): Response
    {
        $membership = $user->organizations->find($election->organization_id);
        
        if ($membership && in_array($membership->pivot->role, ['admin', 'election_officer', 'super_admin'])) {
             return Response::allow();
        }

        return Response::deny('You are not authorized to manage this election.');
    }
}
