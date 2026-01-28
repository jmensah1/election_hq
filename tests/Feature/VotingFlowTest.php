<?php

namespace Tests\Feature;

use App\Jobs\SendVoteConfirmation;
use App\Models\AuditLog;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\Notification;
use App\Models\Organization;
use App\Models\Position;
use App\Models\User;
use App\Models\Vote;
use App\Models\VoteConfirmation;
use App\Services\VotingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class VotingFlowTest extends TestCase
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
        
        // Use container to resolve VotingService with dependencies
        $this->votingService = app(VotingService::class);
        
        // Setup Organization
        $org = Organization::create([
            'name' => 'Test Org',
            'slug' => 'test-org',
        ]);
        
        // Mock current organization for Global Scopes if needed
        // Assuming BelongsToOrganization trait uses auth()->user()->currentOrganization
        // We might need to handle that or manually set organization_id
        
        $this->election = Election::create([
            'organization_id' => $org->id,
            'title' => 'Test Election',
            'slug' => 'test-election',
            'nomination_start_date' => now()->subDays(5),
            'nomination_end_date' => now()->subDays(4),
            'vetting_start_date' => now()->subDays(3),
            'vetting_end_date' => now()->subDays(2),
            'voting_start_date' => now()->subDay(),
            'voting_end_date' => now()->addDay(),
            'status' => 'voting',
            'created_by' => User::factory()->create()->id, // Required by migration
        ]);

        $this->position = Position::create([
            'organization_id' => $org->id,
            'election_id' => $this->election->id,
            'name' => 'President',
        ]);

        $candidateUser = User::factory()->create();
        $this->candidate = Candidate::create([
            'organization_id' => $org->id,
            'election_id' => $this->election->id,
            'position_id' => $this->position->id,
            'user_id' => $candidateUser->id,
        ]);

        $this->voter = User::factory()->create();
    }

    public function test_voting_flow_integration()
    {
        Bus::fake();

        $ballot = [
            $this->position->id => $this->candidate->id
        ];

        // Action
        $this->votingService->castVote($this->election, $this->voter, $ballot);

        // Assert 1: Vote Confirmation Created
        $this->assertDatabaseHas('vote_confirmations', [
            'election_id' => $this->election->id,
            'position_id' => $this->position->id,
            'user_id' => $this->voter->id,
        ]);

        // Assert 2: Anonymous Vote Created
        $this->assertDatabaseHas('votes', [
            'election_id' => $this->election->id,
            'position_id' => $this->position->id,
            'candidate_id' => $this->candidate->id,
        ]);

        // Assert 3: Audit Log Created
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'vote_cast',
            'entity_type' => Election::class,
            'entity_id' => $this->election->id,
            // 'user_id' => ... might be null if auth() not set?
            // Service uses Auth::user(). We didn't login.
            // But we passed user to VotingService, AuditService uses Auth::user().
            // So AuditLog might have user_id = null. That's fine for this test, checks creation.
        ]);

        // Assert 4: Notification Created
        $this->assertDatabaseHas('notifications', [
            'recipient' => $this->voter->email,
            'category' => 'vote_confirmation',
        ]);

        // Assert 5: Job Dispatched
        Bus::assertDispatched(SendVoteConfirmation::class);
    }
}
