<?php

namespace Tests\Feature;

use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class MultiTenancyTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_resolves_organization_by_subdomain()
    {
        $org = Organization::factory()->create([
            'subdomain' => 'college',
            'name' => 'College Org',
            'timezone' => 'America/New_York',
        ]);

        Config::set('app.url', 'http://elections-hq.me');

        // Simulate visiting college.elections-hq.me
        $response = $this->get('http://college.elections-hq.me');

        // We expect it to hit the home page (which returns view welcome by default)
        // Check that middleware shared the organization
        $this->assertEquals($org->id, app('current_organization')->id);
        $this->assertEquals('America/New_York', config('app.timezone'));
    }

    public function test_it_resolves_organization_by_custom_domain()
    {
        $org = Organization::factory()->create([
            'custom_domain' => 'vote.college.com',
            'name' => 'College Custom',
        ]);

        $response = $this->get('http://vote.college.com');

        $this->assertEquals($org->id, app('current_organization')->id);
    }

    public function test_it_returns_404_for_unknown_subdomain()
    {
        Config::set('app.url', 'http://elections-hq.me');

        $response = $this->get('http://unknown.elections-hq.me');

        $response->assertStatus(404);
    }
}
