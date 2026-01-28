<?php

namespace Tests\Feature;

use App\Livewire\VotingBooth;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\Organization;
use App\Models\Position;
use App\Models\User;
use App\Models\Vote;
use App\Models\VoteConfirmation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VoterPortalTest extends TestCase
{
    use RefreshDatabase;

    protected $organization;
    protected $user;
    protected $election;
    protected $position;
    protected $candidate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create();
        $this->user->organizations()->attach($this->organization, ['role' => 'voter']);

        $this->election = Election::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'voting',
            'voting_start_date' => now()->subDay(),
            'voting_end_date' => now()->addDay(),
        ]);

        $this->position = Position::factory()->create([
            'election_id' => $this->election->id,
            'max_votes' => 1,
        ]);

        $candidateUser = User::factory()->create();
        $this->candidate = Candidate::factory()->create([
            'position_id' => $this->position->id,
            'user_id' => $candidateUser->id,
            'nomination_status' => 'approved',
            'vetting_status' => 'passed',
        ]);
    }

    public function test_unauthenticated_users_redirected_to_google_login()
    {
        $response = $this->get(route('voter.elections.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_voter_can_view_election_list()
    {
        $response = $this->actingAs($this->user)
            ->withSession(['organization_id' => $this->organization->id]) // Mock middleware context if needed
            ->get(route('voter.elections.index'));

        $response->assertStatus(200);
        $response->assertSee($this->election->title);
    }

    public function test_voter_can_access_voting_booth()
    {
        $response = $this->actingAs($this->user)
            ->get(route('voter.elections.show', $this->election));

        $response->assertStatus(200);
        $response->assertSeeLivewire(VotingBooth::class);
    }

    public function test_voter_can_submit_vote()
    {
        Livewire::actingAs($this->user)
            ->test(VotingBooth::class, ['election' => $this->election])
            ->set('ballot', [$this->position->id => $this->candidate->id])
            ->call('submitVote')
            ->assertRedirect(route('voter.confirmation'));

        $this->assertDatabaseHas('vote_confirmations', [
            'user_id' => $this->user->id,
            'election_id' => $this->election->id,
        ]);

        $this->assertDatabaseHas('votes', [
            'election_id' => $this->election->id,
            'candidate_id' => $this->candidate->id,
        ]);
        
        // Ensure anonymity: Vote record must NOT have user_id
        // Note: The Vote model schema doesn't have user_id column, so we can't asserting it's null 
        // if the column doesn't exist is trivial, but good to check implementation.
    }

    public function test_double_voting_prevention_in_controller()
    {
        // First vote
        VoteConfirmation::create([
            'organization_id' => $this->organization->id,
            'election_id' => $this->election->id,
            'position_id' => $this->position->id,
            'user_id' => $this->user->id,
        ]);

        // Try access booth again
        $response = $this->actingAs($this->user)
            ->get(route('voter.elections.show', $this->election));

        $response->assertRedirect(route('voter.confirmation'));
        $response->assertSessionHas('error');
    }
}
