<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election;
use Illuminate\Http\Request;

class PrintElectionResultsController extends Controller
{
    public function __invoke(Request $request, Election $election)
    {
        // Permission check
        if (!auth()->user()?->is_super_admin && !auth()->user()->can('manage', $election)) {
            abort(403);
        }

        // Only allow printing if completed or results published, unless super admin
        if (!$election->results_published && $election->status !== 'completed' && !auth()->user()?->is_super_admin) {
             abort(403, 'Results are not yet published.');
        }

        $positions = $election->positions()
            ->with(['candidates' => function ($query) {
                $query->where('vetting_status', 'passed')
                      ->orderByDesc('vote_count');
            }])
            ->orderBy('display_order')
            ->get();

        $positionIds = $positions->pluck('id');

        $noVoteCounts = \App\Models\Vote::whereIn('position_id', $positionIds)
            ->where('is_no_vote', true)
            ->selectRaw('position_id, count(*) as aggregate')
            ->groupBy('position_id')
            ->pluck('aggregate', 'position_id');

        $results = $positions->map(function ($position) use ($noVoteCounts) {
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
                // Or simply: max votes.
                
                // Strictly speaking, we should rely on the DB. But "No" isn't in the DB as a candidate.
                // So we calculate it here relative to the single candidate.
                $candidate = $position->candidates->first();
                $isNoWinner = false;
                if ($candidate && $noVotesCount > $candidate->vote_count) {
                    $isNoWinner = true;
                }

                $candidates->push([
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
                'name' => $position->name,
                'totalVotes' => $totalVotes,
                'candidates' => $candidates,
            ];
        });

        return view('admin.elections.print-results', [
            'election' => $election,
            'results' => $results,
        ]);
    }
}
