<?php

namespace App\Services;

use App\Models\Election;
use App\Models\Vote;
use App\Models\VoteConfirmation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ResultsService
{
    /**
     * Calculate vote counts for all candidates in an election.
     *
     * @param Election $election
     * @return Collection
     */
    public function calculateResults(Election $election): Collection
    {
        return Vote::where('election_id', $election->id)
            ->select('candidate_id', 'position_id', DB::raw('count(*) as total_votes'))
            ->groupBy('candidate_id', 'position_id')
            ->get();
    }

    /**
     * Get participation statistics.
     *
     * @param Election $election
     * @return array
     */
    public function getParticipationStats(Election $election): array
    {
        // Total eligible voters (approximate based on OrganizationUser count if all can vote)
        // Ideally we filter OrganizationUser by can_vote = true
        $totalEligible = $election->organization->users() // This relationship might need check, Organization hasMany Users through Pivot
            ->count(); 
            // Note: Organization::users() is BelongsToMany. We need to check pivot 'can_vote'.
            // But for MVP simplicity, let's assume all users in org are potential voters or check pivot.
            
            // Refined:
            $totalEligible = DB::table('organization_user')
                ->where('organization_id', $election->organization_id)
                ->where('can_vote', true)
                ->where('status', 'active')
                ->count();

        // Actual voters (distinct users in vote_confirmations)
        $actualVoters = VoteConfirmation::where('election_id', $election->id)
            ->distinct('user_id')
            ->count('user_id');

        $turnoutPercentage = $totalEligible > 0 
            ? round(($actualVoters / $totalEligible) * 100, 2) 
            : 0;

        return [
            'total_eligible' => $totalEligible,
            'actual_voters' => $actualVoters,
            'turnout_percentage' => $turnoutPercentage,
        ];
    }

    /**
     * Determine winners for the election.
     * (Updates candidate is_winner flag - to be called when election is completed)
     *
     * @param Election $election
     * @return void
     */
    public function determineWinners(Election $election): void
    {
        // Get all positions
        $positions = $election->positions;

        foreach ($positions as $position) {
            $maxWinners = $position->max_candidates ?? 1; // e.g., 1 for President, 2 for Senators? Usually 1 winner per "seat"
             // Wait, max_candidates usually limits nominations. Winners depends on 'max_votes' or is just 1?
             // Architecture doc says "max_votes_per_position" (user choice limit). 
             // Usually it's "First Past The Post". 
             // Let's assume Top 1 for now unless position has `seats` concept. 
             // MVP: 1 winner per position.

            $results = Vote::where('position_id', $position->id)
                ->select('candidate_id', DB::raw('count(*) as total_votes'))
                ->groupBy('candidate_id')
                ->orderByDesc('total_votes')
                ->limit($maxWinners) // Assuming 1 winner
                ->get();

            // Reset previous winners
            $position->candidates()->update(['is_winner' => false]);

            foreach ($results as $result) {
                $position->candidates()
                    ->where('id', $result->candidate_id)
                    ->update(['is_winner' => true]);
            }
        }
    }
}
