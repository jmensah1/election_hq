<?php

namespace App\Http\Controllers\Voter;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\VoteConfirmation;
use Illuminate\Http\Request;

class VotingController extends Controller
{
    public function index()
    {
        $elections = Election::where('status', 'voting')
            ->orderBy('voting_end_date', 'asc')
            ->get();

        return view('voter.elections', compact('elections'));
    }

    public function show(Election $election)
    {
        // Simple eligibility check - more robust checks are in the Livewire component / Policy
        if ($election->status !== 'voting') {
            return redirect()->route('voter.elections.index')
                ->with('error', 'This election is not currently open for voting.');
        }

        // Check if already voted
        $hasVoted = VoteConfirmation::where('election_id', $election->id)
            ->where('user_id', auth()->id())
            ->exists();

        if ($hasVoted) {
             return redirect()->route('voter.confirmation')
                ->with('error', 'You have already voted in this election.');
        }

        return view('voter.vote', compact('election'));
    }

    public function confirmation()
    {
        return view('voter.confirmation');
    }

    public function results(Election $election)
    {
        if (!$election->results_published) {
            abort(403, 'Results have not been published yet.');
        }
        
        // Eager load relationships for efficiency
        $election->load(['positions.candidates.user']);

        return view('voter.results', compact('election'));
    }
}
