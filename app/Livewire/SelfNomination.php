<?php

namespace App\Livewire;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\Position;
use App\Models\OrganizationUser;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;

class SelfNomination extends Component
{
    use WithFileUploads;

    public $elections = [];
    public $selectedElectionId = null;
    public $selectedPositionId = null;
    public $positions = [];
    public $name = '';
    public $manifesto = '';
    public $photo = null;
    public $terms_accepted = false;
    public $isVoter = false;
    public $existingNomination = null;
    public $submitted = false;

    public function mount()
    {
        $user = auth()->user();
        $orgId = function_exists('current_organization_id') ? current_organization_id() : null;

        if (!$user || !$orgId) {
            return;
        }

        $this->name = $user->name;

        // Check if the user is a registered voter in this organization
        $this->isVoter = OrganizationUser::where('organization_id', $orgId)
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('allowed_email', $user->email);
            })
            ->exists();

        if (!$this->isVoter) {
            return;
        }

        // Load elections with self-nomination open
        $this->elections = Election::where('organization_id', $orgId)
            ->where('self_nomination_enabled', true)
            ->where('status', 'nomination')
            ->where('nomination_start_date', '<=', now())
            ->where('nomination_end_date', '>=', now())
            ->get();

        // Check if user already has a nomination in any of these elections
        if ($this->elections->isNotEmpty()) {
            $this->existingNomination = Candidate::where('user_id', $user->id)
                ->whereIn('election_id', $this->elections->pluck('id'))
                ->with(['election', 'position'])
                ->first();

            // If there's only one election, auto-select it
            if ($this->elections->count() === 1 && !$this->existingNomination) {
                $this->selectedElectionId = $this->elections->first()->id;
                $this->loadPositions();
            }
        }
    }

    public function updatedSelectedElectionId()
    {
        $this->selectedPositionId = null;
        $this->loadPositions();
    }

    protected function loadPositions()
    {
        if (!$this->selectedElectionId) {
            $this->positions = collect();
            return;
        }

        $this->positions = Position::where('election_id', $this->selectedElectionId)
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();
    }

    public function submit()
    {
        $user = auth()->user();
        $orgId = function_exists('current_organization_id') ? current_organization_id() : null;

        if (!$user || !$orgId || !$this->isVoter) {
            session()->flash('error', 'You are not authorized to self-nominate.');
            return;
        }

        $this->validate([
            'selectedElectionId' => 'required|exists:elections,id',
            'selectedPositionId' => 'required|exists:positions,id',
            'name' => 'required|string|max:255',
            'manifesto' => 'required|min:50',
            'photo' => 'nullable|image|max:2048',
            'terms_accepted' => 'accepted',
        ], [
            'selectedElectionId.required' => 'Please select an election.',
            'selectedPositionId.required' => 'Please select a position.',
            'name.required' => 'Please provide your name.',
            'manifesto.required' => 'Please provide your manifesto or campaign statement.',
            'manifesto.min' => 'Your manifesto must be at least 50 characters.',
            'terms_accepted.accepted' => 'You must agree to the election rules and regulations.',
        ]);

        // Verify the election still accepts self-nominations
        $election = Election::find($this->selectedElectionId);
        if (!$election || !$election->isSelfNominationOpen()) {
            session()->flash('error', 'Self-nomination is no longer open for this election.');
            return;
        }

        // Verify the position belongs to the selected election
        $position = Position::where('id', $this->selectedPositionId)
            ->where('election_id', $this->selectedElectionId)
            ->where('is_active', true)
            ->first();

        if (!$position) {
            session()->flash('error', 'Invalid position selected.');
            return;
        }

        // Check: one nomination per election per candidate
        $existingInElection = Candidate::where('user_id', $user->id)
            ->where('election_id', $this->selectedElectionId)
            ->exists();

        if ($existingInElection) {
            session()->flash('error', 'You have already nominated yourself for a position in this election. Each candidate may only nominate for one position.');
            return;
        }

        // Handle photo upload
        $photoPath = null;
        if ($this->photo) {
            $photoPath = $this->photo->store('candidates/photos', 'public');
        }

        // Update user name
        $user->update(['name' => $this->name]);

        // Create the candidate record
        Candidate::create([
            'organization_id' => $orgId,
            'election_id' => $this->selectedElectionId,
            'position_id' => $this->selectedPositionId,
            'user_id' => $user->id,
            'email' => $user->email,
            'manifesto' => $this->manifesto,
            'photo_path' => $photoPath,
            'nomination_status' => 'pending_vetting',
            'nominated_at' => now(),
            'nominated_by' => null, // Self-nominated
        ]);

        $this->submitted = true;
        $this->existingNomination = Candidate::where('user_id', $user->id)
            ->where('election_id', $this->selectedElectionId)
            ->with(['election', 'position'])
            ->first();

        session()->flash('success', 'Your nomination has been submitted successfully! The Electoral Commission will review your application.');
    }

    #[Layout('layouts.voter', ['title' => 'Self Nomination'])]
    public function render()
    {
        return view('livewire.self-nomination');
    }
}
