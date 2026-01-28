<?php

namespace App\Livewire;

use App\Models\Candidate;
use Livewire\Component;
use Livewire\WithFileUploads;

class CandidatePortal extends Component
{
    use WithFileUploads;

    public $candidate;
    public $manifesto;
    public $photo;
    public $terms_accepted = false;

    public function mount()
    {
        $user = auth()->user();
        
        // 1. Try to find by existing link (Primary Method)
        $this->candidate = Candidate::where('user_id', $user->id)
            ->whereIn('nomination_status', ['pending_submission', 'pending_vetting'])
            ->latest()
            ->first();

        // 2. Fallback: Try to find by EMAIL (Invitation) and claim it
        // SECURITY FIX: Only allow claiming if the user's email is strictly verified.
        // This protects against unverified accounts claiming profiles.
        if (!$this->candidate && $user->hasVerifiedEmail()) {
            
            $invitation = Candidate::where('email', $user->email)
                ->whereNull('user_id') // Only unclaimed ones
                ->whereIn('nomination_status', ['pending_submission'])
                ->latest()
                ->first();
            
            if ($invitation) {
                // Link the user to this candidate record
                $invitation->update(['user_id' => $user->id]);
                $this->candidate = $invitation;
            }
        }

        // 3. Fallback for read-only view of past/processed applications
        if (!$this->candidate) {
            $this->candidate = Candidate::where('user_id', $user->id)
                ->latest()
                ->first();
        }

        if ($this->candidate) {
            $this->manifesto = $this->candidate->manifesto;
        }
    }

    public function render()
    {
        return view('livewire.candidate-portal')->layout('layouts.app'); 
        // We might need a simple layout if layouts.app doesn't exist or is for something else.
        // Usually Livewire looks for components.layouts.app
    }
}
