<?php

namespace Tests\Unit\Services;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\Organization;
use App\Models\Position;
use App\Models\User;
use App\Models\Vote;
use App\Services\ResultsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculate_results()
    {
        $org = Organization::create(['name' => 'T', 'slug' => 't']);
        $election = Election::create(['organization_id' => $org->id, 'title' => 'E', 'slug' => 'e', 'nomination_start_date' => now(), 'nomination_end_date' => now(), 'vetting_start_date' => now(), 'vetting_end_date' => now(), 'voting_start_date' => now(), 'voting_end_date' => now(), 'created_by' => User::factory()->create()->id]);
        $position = Position::create(['organization_id' => $org->id, 'election_id' => $election->id, 'name' => 'P']);
        
        $c1 = Candidate::create(['organization_id' => $org->id, 'election_id' => $election->id, 'position_id' => $position->id, 'user_id' => User::factory()->create()->id]);
        $c2 = Candidate::create(['organization_id' => $org->id, 'election_id' => $election->id, 'position_id' => $position->id, 'user_id' => User::factory()->create()->id]);

        // Cast 3 votes for C1, 1 vote for C2
        Vote::factory()->count(3)->create(['candidate_id' => $c1->id, 'position_id' => $position->id, 'election_id' => $election->id, 'organization_id' => $org->id]);
        Vote::factory()->count(1)->create(['candidate_id' => $c2->id, 'position_id' => $position->id, 'election_id' => $election->id, 'organization_id' => $org->id]);

        $service = new ResultsService();
        $results = $service->calculateResults($election);

        $this->assertEquals(2, $results->count());
        $this->assertEquals(3, $results->where('candidate_id', $c1->id)->first()->total_votes);
        $this->assertEquals(1, $results->where('candidate_id', $c2->id)->first()->total_votes);
    }
}
