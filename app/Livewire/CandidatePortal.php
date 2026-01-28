<?php

namespace App\Livewire;

use App\Models\Candidate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

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
        
        // 1. Try to find by existing link
        $this->candidate = Candidate::where('user_id', $user->id)
            ->whereIn('nomination_status', ['pending_submission', 'pending_vetting'])
            ->latest()
            ->first();

        // 2. If not found, try to find by EMAIL (Invitation) and claim it
        if (!$this->candidate) {
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
        $this->validate([
            'manifesto' => 'required|min:50|max:5000',
            'photo' => 'nullable|image|max:2048', // 2MB
            'terms_accepted' => 'accepted',
        ]);

        if (!$this->candidate) {
            return;
        }

        $data = [
            'manifesto' => $this->manifesto,
            'nomination_status' => 'pending_vetting',
        ];

        if ($this->photo) {
            $path = $this->photo->store('candidates/photos', 'public');
            $data['photo_path'] = $path;
        }

        $this->candidate->update($data);

        session()->flash('message', 'Nomination submitted successfully! Use the "Refresh" button to check your status.');
        $this->redirect(request()->header('Referer'));
    }

    public function render()
    {
        return view('livewire.candidate-portal')->layout('layouts.app'); 
        // We might need a simple layout if layouts.app doesn't exist or is for something else.
        // Usually Livewire looks for components.layouts.app
    }
}
