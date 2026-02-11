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

        $results = $positions->map(function ($position) {
            $totalVotes = $position->candidates->sum('vote_count');
            
            return [
                'name' => $position->name,
                'totalVotes' => $totalVotes,
                'candidates' => $position->candidates->map(function ($candidate) use ($totalVotes) {
                    $percentage = $totalVotes > 0 ? round(($candidate->vote_count / $totalVotes) * 100, 1) : 0;
                    
                    return [
                        'name' => $candidate->user?->name ?? $candidate->email,
                        'photo' => $candidate->photo_path,
                        'votes' => $candidate->vote_count,
                        'percentage' => $percentage,
                        'isWinner' => $candidate->is_winner,
                    ];
                }),
            ];
        });

        return view('admin.elections.print-results', [
            'election' => $election,
            'results' => $results,
        ]);
    }
}
