<?php

namespace App\Livewire;

use App\Models\Election;
use App\Models\Position;
use App\Models\Candidate;
use App\Services\VotingService;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class VotingBooth extends Component
{
    public Election $election;
    public $positions = [];
    public $currentStep = 0;
    public $ballot = []; // Format: [position_id => candidate_id]
    public $reviewing = false;

    public function mount(Election $election)
    {
        $this->election = $election;
        // Load positions with candidates that are approved/passed
        $this->positions = $election->positions()
            ->with(['candidates' => function ($query) {
                $query->where('nomination_status', 'approved')
                      ->where('vetting_status', 'passed')
                      ->reorder()
                      ->orderBy('ballot_order')
                      ->orderBy('id');
            }, 'candidates.user'])
            ->orderBy('display_order')
            ->get();
            
        // Initialize ballot with nulls or existing selections if any
        foreach ($this->positions as $position) {
            $key = 'pos_' . $position->id;
            if (!isset($this->ballot[$key])) {
                $this->ballot[$key] = null;
            }
        }
    }

    public function selectCandidate($positionId, $candidateId)
    {
        $this->ballot[$positionId] = $candidateId;
    }

    public function selectYesNo($positionId, $vote)
    {
        // $vote is either 'yes' or 'no'
        $this->ballot[$positionId] = $vote;
    }

    public function isYesNoPosition($position): bool
    {
        // Position is Yes/No if explicitly marked OR has only one approved candidate
        if ($position->is_yes_no_vote) {
            return true;
        }
        
        // Auto-detect: single candidate positions become Yes/No
        return $position->candidates->count() === 1;
    }

    public function nextStep()
    {
        // Validation for current step
        $currentPosition = $this->positions[$this->currentStep];
        $isYesNo = $this->isYesNoPosition($currentPosition);
        
        if ($isYesNo) {
            $this->validate([
                "ballot.pos_{$currentPosition->id}" => 'required|in:yes,no',
            ], [
                "ballot.pos_{$currentPosition->id}.required" => 'Please select Yes or No before proceeding.',
                "ballot.pos_{$currentPosition->id}.in" => 'Please select Yes or No before proceeding.',
            ]);
        } else {
            $this->validate([
                "ballot.pos_{$currentPosition->id}" => 'required|exists:candidates,id',
            ], [
                "ballot.pos_{$currentPosition->id}.required" => 'Please select a candidate before proceeding.',
            ]);

            // Verify candidate belongs to position (extra safety)
            $candidateId = $this->ballot['pos_' . $currentPosition->id];
            $candidate = Candidate::find($candidateId);
            if ($candidate->position_id !== $currentPosition->id) {
                $this->addError("ballot.pos_{$currentPosition->id}", "Invalid candidate selected.");
                return;
            }
        }

        if ($this->currentStep < count($this->positions) - 1) {
            $this->currentStep++;
        } else {
            $this->reviewing = true;
        }
    }

    public function previousStep()
    {
        if ($this->reviewing) {
            $this->reviewing = false;
        } elseif ($this->currentStep > 0) {
            $this->currentStep--;
        }
    }
    
    public function jumpToStep($stepIndex)
    {
        // Allow jumping back, but not forward beyond what's completed + 1?
        // Simpler for now: allow jumping any step for navigation if reviewing, 
        // otherwise strict sequential.
        if ($this->reviewing) {
            $this->reviewing = false;
            $this->currentStep = $stepIndex;
        }
    }

    public function submitVote(VotingService $votingService)
    {
        // Build clean ballot separating candidate votes from yes/no votes
        $cleanBallot = [];
        $yesNoVotes = [];
        
        foreach ($this->positions as $position) {
            $key = 'pos_' . $position->id;
            $value = $this->ballot[$key] ?? null;
            
            if ($value === null) {
                $this->addError($key, 'Please make a selection for all positions.');
                return;
            }
            
            if ($this->isYesNoPosition($position)) {
                // Yes/No vote
                $yesNoVotes[$position->id] = [
                    'vote' => $value, // 'yes' or 'no'
                    'candidate_id' => $position->candidates->first()?->id,
                ];
            } else {
                // Regular candidate vote
                $cleanBallot[$position->id] = $value;
            }
        }

        try {
            $votingService->castVote($this->election, auth()->user(), $cleanBallot, $yesNoVotes);
            return redirect()->route('voter.confirmation');
        } catch (\Exception $e) {
            session()->flash('error', 'Error submitting vote: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.voting-booth');
    }
}
