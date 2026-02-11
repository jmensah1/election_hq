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
            // Get vote counts for all candidates in this position
            $results = Vote::where('position_id', $position->id)
                ->select('candidate_id', DB::raw('count(*) as total_votes'))
                ->groupBy('candidate_id')
                ->orderByDesc('total_votes')
                ->get();

            // Reset previous winners
            $position->candidates()->update(['is_winner' => false]);

            if ($results->isEmpty()) {
                continue;
            }

            // Determine the highest vote count
            $maxVotes = $results->first()->total_votes;
            
            // Allow for a "Draw" - if multiple candidates have maxVotes, they are all winners
            // This is "First Past The Post" but handles ties by declaring multiple winners (no run-off logic yet)
            $winnerIds = $results->filter(function ($result) use ($maxVotes) {
                return $result->total_votes == $maxVotes;
            })->pluck('candidate_id');

            // Mark winners
            if ($winnerIds->isNotEmpty()) {
                $position->candidates()
                    ->whereIn('id', $winnerIds)
                    ->update(['is_winner' => true]);
            }
        }
    }
}
