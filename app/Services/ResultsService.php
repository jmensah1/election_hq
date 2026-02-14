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
     * Get comprehensive results for an election, including "No" votes and formatting.
     *
     * @param Election $election
     * @return Collection
     */
    public function getElectionResults(Election $election): Collection
    {
        $positions = $election->positions()
            ->with(['candidates' => function ($query) {
                // Ensure we only show vetted candidates in final results
                $query->where('vetting_status', 'passed')
                      ->orderByDesc('vote_count');
            }])
            ->orderBy('display_order')
            ->get();

        $positionIds = $positions->pluck('id');

        $noVoteCounts = Vote::whereIn('position_id', $positionIds)
            ->where('is_no_vote', true)
            ->selectRaw('position_id, count(*) as aggregate')
            ->groupBy('position_id')
            ->pluck('aggregate', 'position_id');

        return $positions->map(function ($position) use ($noVoteCounts) {
            $totalVotes = $position->candidates->sum('vote_count');
            
            // Calculate No votes if applicable
            $noVotesCount = 0;
            if ($position->is_yes_no_vote) {
                $noVotesCount = (int) ($noVoteCounts[$position->id] ?? 0);
                $totalVotes += $noVotesCount;
            }

            $candidates = $position->candidates->map(function ($candidate) use ($totalVotes) {
                $percentage = $totalVotes > 0 ? round(($candidate->vote_count / $totalVotes) * 100, 1) : 0;
                
                return [
                    'id' => $candidate->id,
                    'name' => $candidate->user?->name ?? $candidate->email,
                    'photo' => $candidate->photo_path,
                    'votes' => $candidate->vote_count,
                    'percentage' => $percentage,
                    'isWinner' => $candidate->is_winner,
                    'isNoVote' => false,
                ];
            });

            // Append "No" option if applicable
            if ($position->is_yes_no_vote) {
                $percentage = $totalVotes > 0 ? round(($noVotesCount / $totalVotes) * 100, 1) : 0;
                
                // Determine if No is winner (simple view logic: if No has more votes than the candidate)
                // Note: The service ensures the candidate is NOT a winner if No > Yes.
                // So if the candidate is not a winner, and it's a Yes/No vote, and No > Yes, then No is the winner.
                $candidate = $position->candidates->first();
                $isNoWinner = false;
                if ($candidate && $noVotesCount > $candidate->vote_count) {
                    $isNoWinner = true;
                }

                $candidates->push([
                    'id' => 'no_vote_' . $position->id,
                    'name' => 'No',
                    'photo' => null, // Could use a "X" icon or placeholder
                    'votes' => $noVotesCount,
                    'percentage' => $percentage,
                    'isWinner' => $isNoWinner,
                    'isNoVote' => true,
                ]);
            }
            
            // Sort by votes descending
            $candidates = $candidates->sortByDesc('votes')->values();
            
            return [
                'position' => $position, // Keep the model reference if needed
                'name' => $position->name,
                'totalVotes' => $totalVotes,
                'candidates' => $candidates,
            ];
        });
    }

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
        // Total eligible voters
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
            $maxWinners = $position->max_candidates ?? 1; 

            // Get vote counts for all candidates in this position
            $results = Vote::where('position_id', $position->id)
                ->select('candidate_id', 'is_no_vote', DB::raw('count(*) as total_votes'))
                ->groupBy('candidate_id', 'is_no_vote')
                ->get();

            // Reset previous winners
            $position->candidates()->update(['is_winner' => false]);

            if ($results->isEmpty()) {
                continue;
            }

            if ($position->is_yes_no_vote) {
                // Yes/No Logic
                // We assume 1 candidate for Yes/No (the subject)
                $candidateId = $results->first()->candidate_id;
                
                $yesVotes = $results->where('is_no_vote', false)->sum('total_votes');
                $noVotes = $results->where('is_no_vote', true)->sum('total_votes');

                if ($yesVotes > $noVotes) {
                    $position->candidates()
                        ->where('id', $candidateId)
                        ->update(['is_winner' => true]);
                }
                // If No >= Yes, nobody wins (conceptually "No" wins, but candidate is not winner)
            } else {
                // Regular Election Logic (First Past The Post)
                $candidatesVotes = $results->groupBy('candidate_id')->map(function ($rows) {
                    return $rows->sum('total_votes');
                });

                if ($candidatesVotes->isEmpty()) {
                    continue;
                }

                $maxVotes = $candidatesVotes->max();

                // Allow for ties
                $winnerIds = $candidatesVotes->filter(function ($votes) use ($maxVotes) {
                    return $votes == $maxVotes;
                })->keys();

                if ($winnerIds->isNotEmpty()) {
                    $position->candidates()
                        ->whereIn('id', $winnerIds)
                        ->update(['is_winner' => true]);
                }
            }
        }
    }
}
