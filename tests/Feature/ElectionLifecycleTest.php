<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class ElectionLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_election_transitions_from_draft_to_nomination()
    {
        $election = Election::factory()->create([
            'status' => 'draft',
            'nomination_start_date' => now()->subMinute(),
            'nomination_end_date' => now()->addDay(),
        ]);

        $this->artisan('elections:update-statuses')
            ->assertSuccessful();

        $this->assertEquals('nomination', $election->fresh()->status);
    }

    public function test_election_transitions_to_vetting()
    {
        $election = Election::factory()->create([
            'status' => 'nomination',
            'nomination_start_date' => now()->subDays(2),
            'nomination_end_date' => now()->subDay(),
            'vetting_start_date' => now()->subMinute(),
            'vetting_end_date' => now()->addDay(),
        ]);

        $this->artisan('elections:update-statuses')
            ->assertSuccessful();

        $this->assertEquals('vetting', $election->fresh()->status);
    }

    public function test_election_transitions_to_voting()
    {
        $election = Election::factory()->create([
            'status' => 'vetting',
            'voting_start_date' => now()->subMinute(),
            'voting_end_date' => now()->addDay(),
        ]);

        $this->artisan('elections:update-statuses')
            ->assertSuccessful();

        $this->assertEquals('voting', $election->fresh()->status);
    }

    public function test_election_transitions_to_completed()
    {
        $election = Election::factory()->create([
            'status' => 'voting',
            'voting_start_date' => now()->subDays(2),
            'voting_end_date' => now()->subMinute(),
        ]);

        $this->artisan('elections:update-statuses')
            ->assertSuccessful();

        $this->assertEquals('completed', $election->fresh()->status);
    }

    public function test_nomination_is_open_check()
    {
        $election = Election::factory()->create([
            'status' => 'nomination',
            'nomination_start_date' => now()->subMinute(),
            'nomination_end_date' => now()->addDay(),
        ]);

        $this->assertTrue($election->isNominationOpen());

        // Closed by date
        $election->update(['nomination_end_date' => now()->subMinute()]);
        $this->assertFalse($election->isNominationOpen());

        // Closed by status
        $election->update([
            'nomination_end_date' => now()->addDay(),
            'status' => 'vetting'
        ]);
        $this->assertFalse($election->isNominationOpen());
    }

    public function test_candidate_cannot_submit_after_deadline()
    {
        $user = User::factory()->create();
        $election = Election::factory()->create([
            'status' => 'nomination',
            'nomination_start_date' => now()->subDays(2),
            'nomination_end_date' => now()->subMinute(), // Deadline passed
        ]);

        $candidate = Candidate::factory()->create([
            'user_id' => $user->id,
            'election_id' => $election->id,
            'nomination_status' => 'pending_submission',
        ]);

        $this->actingAs($user);

        Livewire::test(\App\Livewire\CandidatePortal::class)
            ->set('manifesto', 'This is a long enough manifesto for the candidate submission.')
            ->set('terms_accepted', true)
            ->call('submit')
            ->assertHasErrors(); // Should have error flashed or handled, verifying error message session
            
        // Check session error manually since assertHasErrors checks validation errors
        // The component flashes to session 'error'
        // Livewire testing doesn't easily assert session flash in the same way as HTTP tests unless we check the view or session directly?
        // Actually assertSessionHas works for HTTP, for Livewire it's implicit or we check dispatched events.
        // The component does: session()->flash('error', ...)
        
        // Let's verify the candidate status didn't change
        $this->assertEquals('pending_submission', $candidate->fresh()->nomination_status);
    }
}
