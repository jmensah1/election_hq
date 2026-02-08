<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use App\Models\Election;
use App\Services\PlanLimitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_plan_voter_limit()
    {
        $service = app(PlanLimitService::class);
        $org = Organization::factory()->create([
            'subscription_plan' => 'free',
        ]);

        // Add 50 voters
        $users = User::factory()->count(50)->create();
        foreach ($users as $user) {
            $org->users()->attach($user->id, [
                'role' => 'voter',
                'allowed_email' => $user->email,
                'voter_id' => 'V' . $user->id,
                'status' => 'active',
                'can_vote' => true
            ]);
        }
        
        // 50 voters = limit reached? 
        // Logic is: count < max_voters. 50 < 50 is false. So 50th voter is allowed, 51st is not?
        // Wait, if limit is 50, and I have 50, then count is 50. 50 < 50 is false.
        // So I cannot add the 51st voter.
        
        $this->assertFalse($service->canAddVoter($org));
    }

    public function test_free_plan_allows_under_limit()
    {
        $service = app(PlanLimitService::class);
        $org = Organization::factory()->create([
            'subscription_plan' => 'free',
        ]);

        // Add 49 voters
        $users = User::factory()->count(49)->create();
        foreach ($users as $user) {
            $org->users()->attach($user->id, [
                'role' => 'voter', 
                'allowed_email' => $user->email,
                 'voter_id' => 'V' . $user->id,
                'status' => 'active',
                'can_vote' => true
            ]);
        }
        
        $this->assertTrue($service->canAddVoter($org));
    }

    public function test_upgrade_lifts_limit()
    {
        $service = app(PlanLimitService::class);
        $org = Organization::factory()->create([
            'subscription_plan' => 'free',
        ]);

        $users = User::factory()->count(50)->create();
        foreach ($users as $user) {
            $org->users()->attach($user->id, [
                'role' => 'voter', 
                'allowed_email' => $user->email,
                 'voter_id' => 'V' . $user->id,
                'status' => 'active',
                'can_vote' => true
            ]);
        }

        $this->assertFalse($service->canAddVoter($org));

        $org->update(['subscription_plan' => 'basic']); // Basic has 500
        $this->assertTrue($service->canAddVoter($org));
    }

    public function test_election_limit()
    {
        $service = app(PlanLimitService::class);
        $org = Organization::factory()->create([
            'subscription_plan' => 'free', // Limit 1
        ]);

        Election::factory()->create([
            'organization_id' => $org->id,
            'status' => 'voting', // Should count
        ]);

        $this->assertFalse($service->canCreateElection($org));

        // Create a completed one
        Election::factory()->create([
            'organization_id' => $org->id,
            'status' => 'completed',
        ]);
        
        // Still has 1 active, so limit checks active.
        // wait, limit is 1 active election.
        // I have 1 active. 1 < 1 is false. Cannot create another.
        // The completed one shouldn't affect it. 
        
        // Verify completed doesn't count
        $org->elections()->delete();
        Election::factory()->create([
            'organization_id' => $org->id,
            'status' => 'completed',
        ]);
        $this->assertTrue($service->canCreateElection($org));
    }

    public function test_feature_flags()
    {
        $service = app(PlanLimitService::class);
        $org = Organization::factory()->create(['subscription_plan' => 'free']);
        
        $this->assertFalse($service->canUseCustomDomain($org));
        $this->assertFalse($service->canUseSMS($org));

        $org->update(['subscription_plan' => 'basic']);
        $this->assertTrue($service->canUseCustomDomain($org));
        $this->assertFalse($service->canUseSMS($org));

        $org->update(['subscription_plan' => 'premium']);
        $this->assertTrue($service->canUseCustomDomain($org));
        $this->assertTrue($service->canUseSMS($org));
    }
}
