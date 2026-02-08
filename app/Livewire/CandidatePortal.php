<?php

namespace App\Livewire;

use App\Models\Candidate;
use Livewire\Component;
use Livewire\WithFileUploads;

class CandidatePortal extends Component
{
    use WithFileUploads;

    public $candidate;
    public $name;
    public $manifesto;
    public $photo;
    public $terms_accepted = false;

    public function mount()
    {
        $user = auth()->user();
        $this->name = $user->name;
        
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

    public function submit()
    {
        // Check if nomination is still open
        if (!$this->candidate || !$this->candidate->election) {
            session()->flash('error', 'Could not find your nomination record.');
            return;
        }

        $election = $this->candidate->election;
        
        if (!$election->isNominationOpen()) {
            $message = $election->status !== 'nomination' 
                ? 'The nomination period has not started or has ended.'
                : 'The nomination deadline has passed. Submissions are no longer accepted.';
            
            session()->flash('error', $message);
            return;
        }

        $this->validate([
            'name' => 'required|string|max:255',
            'manifesto' => 'required|min:50',
            'photo' => 'nullable|image|max:2048',
            'terms_accepted' => 'accepted',
        ], [
            'name.required' => 'Please provide your name.',
            'manifesto.required' => 'Please provide your manifesto or campaign statement.',
            'manifesto.min' => 'Your manifesto must be at least 50 characters.',
            'terms_accepted.accepted' => 'You must agree to the election rules and regulations.',
        ]);

        // Handle photo upload
        if ($this->photo) {
            $photoPath = $this->photo->store('candidates/photos', 'public');
            $this->candidate->photo_path = $photoPath;
        }

        // Update candidate
        auth()->user()->update(['name' => $this->name]);
        $this->candidate->manifesto = $this->manifesto;
        $this->candidate->nomination_status = 'pending_vetting';
        $this->candidate->save();

        session()->flash('success', 'Your nomination has been submitted successfully! The Electoral Commission will review your application.');

        // Refresh the candidate data
        $this->candidate = $this->candidate->fresh();
    }

    public function render()
    {
        return view('livewire.candidate-portal')->layout('layouts.voter', ['title' => 'Candidate Portal']); 
    }
}
