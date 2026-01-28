<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\Organization;
use App\Models\Position;
use App\Models\User;
use App\Models\Vote;
use App\Models\VoteConfirmation;
use App\Services\VotingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Exceptions\AlreadyVotedException;

class VotingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected VotingService $votingService;
    protected Election $election;
    protected Position $position;
    protected Candidate $candidate;
    protected User $voter;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->votingService = new VotingService();
        
        // Setup Organization
        $org = Organization::create([
            'name' => 'Test Org',
            'slug' => 'test-org',
        ]);

        // Setup Election
        $this->election = Election::create([
            'organization_id' => $org->id,
            'title' => 'Test Election',
            'slug' => 'test-election',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'status' => 'voting',
        ]);

        // Setup Position
        $this->position = Position::create([
            'organization_id' => $org->id,
            'election_id' => $this->election->id,
            'name' => 'President',
        ]);

        // Setup User (Candidate)
        $candidateUser = User::factory()->create();
        
        // Setup Candidate
        $this->candidate = Candidate::create([
            'organization_id' => $org->id,
            'election_id' => $this->election->id,
            'position_id' => $this->position->id,
            'user_id' => $candidateUser->id,
        ]);

        // Setup Voter
        $this->voter = User::factory()->create();
    }

    public function test_voter_can_cast_vote_successfully()
    {
        $ballot = [
            $this->position->id => $this->candidate->id
        ];

        $this->votingService->castVote($this->election, $this->voter, $ballot);

        // Check VoteConfirmation
        $this->assertDatabaseHas('vote_confirmations', [
            'section_id' => null, // Assuming no section_id in schema provided but checking broadly
            'election_id' => $this->election->id,
            'position_id' => $this->position->id,
            'user_id' => $this->voter->id,
        ]);

        // Check Vote (Anonymous)
        $this->assertDatabaseHas('votes', [
            'election_id' => $this->election->id,
            'position_id' => $this->position->id,
            'candidate_id' => $this->candidate->id,
        ]);
        
        // Ensure NO user_id in votes (Schema check implicit by column absence or null, 
        // strictly speaking standard `assertDatabaseHas` checks exact match, 
        // to verify anonymity we ensure we can't find a row with user_id if that column even existed,
        // but since schema definition is key, we rely on the migration not having it.)
    }

    public function test_voter_cannot_vote_twice_for_same_position()
    {
        $ballot = [
            $this->position->id => $this->candidate->id
        ];

        // First vote
        $this->votingService->castVote($this->election, $this->voter, $ballot);

        // Expect Exception on second vote
        $this->expectException(AlreadyVotedException::class);
        
        // Second vote
        $this->votingService->castVote($this->election, $this->voter, $ballot);
    }
}
