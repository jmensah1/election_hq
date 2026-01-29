<?php

namespace Tests\Feature\Livewire;

use App\Livewire\VotingBooth;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\Organization;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VotingBoothTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_persists_candidate_selection_across_steps()
    {
        // Setup
        $org = Organization::factory()->create(['subdomain' => 'test-voting']);
        $user = User::factory()->create();
        $org->users()->attach($user->id, ['role' => 'voter', 'voter_id' => 'V123', 'allowed_email' => $user->email]);
        
        $election = Election::factory()->create(['organization_id' => $org->id, 'status' => 'voting']);
        
        $pos1 = Position::factory()->create(['election_id' => $election->id, 'display_order' => 1]);
        $pos2 = Position::factory()->create(['election_id' => $election->id, 'display_order' => 2]);
        
        $cand1 = Candidate::factory()->create(['position_id' => $pos1->id, 'nomination_status' => 'approved', 'vetting_status' => 'passed']);
        $cand2 = Candidate::factory()->create(['position_id' => $pos2->id, 'nomination_status' => 'approved', 'vetting_status' => 'passed']);

        // Acting as user
        $this->actingAs($user);

        // Debug assertions
        $this->assertEquals(2, $election->positions->count());

        Livewire::test(VotingBooth::class, ['election' => $election])
            ->set('ballot.pos_' . $pos1->id, $cand1->id) // Select first candidate
            ->call('nextStep') // Move to next
            ->assertSet('currentStep', 1)
            ->set('ballot.pos_' . $pos2->id, $cand2->id) // Select second candidate
            ->assertSet('ballot.pos_' . $pos1->id, $cand1->id) // Assert first selection still exists
            ->assertSet('ballot.pos_' . $pos2->id, $cand2->id); // Assert second selection exists
    }
}
