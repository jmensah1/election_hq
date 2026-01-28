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
                      ->where('vetting_status', 'passed');
            }, 'candidates.user'])
            ->orderBy('display_order')
            ->get();
            
        // Initialize ballot with nulls or existing selections if any
        foreach ($this->positions as $position) {
            if (!isset($this->ballot[$position->id])) {
                $this->ballot[$position->id] = null;
            }
        }
    }

    public function selectCandidate($positionId, $candidateId)
    {
        $this->ballot[$positionId] = $candidateId;
    }

    public function nextStep()
    {
        // Validation for current step
        $currentPosition = $this->positions[$this->currentStep];
        
        $this->validate([
            "ballot.{$currentPosition->id}" => 'required|exists:candidates,id',
        ], [
            "ballot.{$currentPosition->id}.required" => 'Please select a candidate before proceeding.',
        ]);

        // Verify candidate belongs to position (extra safety)
        $candidateId = $this->ballot[$currentPosition->id];
        $candidate = Candidate::find($candidateId);
        if ($candidate->position_id !== $currentPosition->id) {
            $this->addError("ballot.{$currentPosition->id}", "Invalid candidate selected.");
            return;
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
        // Final validation
        $this->validate([
            'ballot' => 'required|array',
            'ballot.*' => 'required|integer|exists:candidates,id',
        ]);

        try {
            $votingService->castVote($this->election, auth()->user(), $this->ballot);
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
