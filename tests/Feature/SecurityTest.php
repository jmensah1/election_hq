<?php

namespace Tests\Feature;

use App\Models\Election;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;
    protected User $user;
    protected Election $election;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create Org and User
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create();
        $this->organization->users()->attach($this->user->id, [
            'role' => 'voter',
            'status' => 'active',
            'voter_id' => '12345',
            'allowed_email' => $this->user->email
        ]);

        $this->election = Election::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'voting',
            'results_published' => false
        ]);
        
        // Set context
        $this->actingAs($this->user);
        Config::set('app.url', 'http://' . $this->organization->slug . '.elections-hq.test');
    }

    public function test_vote_submission_is_rate_limited()
    {
        RateLimiter::clear('vote:'.$this->user->id);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('voter.elections.store', $this->election), [
                'votes' => [] // Invalid data but hits rate limiter first
            ]);
        }

        // 6th attempt should fail
        $response = $this->post(route('voter.elections.store', $this->election), [
            'votes' => []
        ]);

        $response->assertStatus(429);
    }

    public function test_results_page_strictly_authorized()
    {
        // 1. Published = False -> Forbidden
        $response = $this->get(route('voter.results', $this->election));
        $response->assertStatus(403);

        // 2. Published = True -> Allowed
        $this->election->update(['results_published' => true]);
        
        $response = $this->get(route('voter.results', $this->election));
        $response->assertStatus(200);
    }
}
