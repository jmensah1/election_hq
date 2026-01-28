<?php

namespace Tests\Feature\Auth;

use App\Models\Organization;
use App\Models\OrganizationUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;
use App\Exceptions\IneligibleVoterException;

class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Mock Socialite
        
    }

    public function test_guest_cannot_login_if_not_on_guest_list()
    {
        // 1. Create Organization
        $org = Organization::factory()->create([
            'subdomain' => 'univ',
            'status' => 'active'
        ]);

        // 2. Mock Google User
        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
        $abstractUser->shouldReceive('getId')->andReturn('google-123');
        $abstractUser->shouldReceive('getName')->andReturn('John Doe');
        $abstractUser->shouldReceive('getEmail')->andReturn('john@example.com');
        $abstractUser->shouldReceive('getAvatar')->andReturn('https://avatar.com/john.jpg');

        Socialite::shouldReceive('driver->user')->andReturn($abstractUser);

        // 3. Attempt Login via Callback
        // We simulate the request coming to the subdomain
        $response = $this->get('http://univ.elections-hq.test/auth/google/callback');

        // 4. Assert Redirect back to login with error
        // The controller catches the exception and redirects to login
        // OR checks if it renders an error page.
        // In our controller we catch config IneligibleVoterException
        
        $response->assertRedirect(route('login'));
        // Session should have error
        $response->assertSessionHas('error');
    }

    public function test_allowed_voter_can_login()
    {
        // 1. Create Organization
        $org = Organization::factory()->create([
            'subdomain' => 'univ',
            'status' => 'active'
        ]);

        // 2. Add email to guest list
        OrganizationUser::create([
            'organization_id' => $org->id,
            'voter_id' => '12345',
            'allowed_email' => 'jane@example.com',
            'role' => 'voter',
            'status' => 'pending'
        ]);

        // 3. Mock Google User
        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
        $abstractUser->shouldReceive('getId')->andReturn('google-456');
        $abstractUser->shouldReceive('getName')->andReturn('Jane Doe');
        $abstractUser->shouldReceive('getEmail')->andReturn('jane@example.com'); // Match!
        $abstractUser->shouldReceive('getAvatar')->andReturn('https://avatar.com/jane.jpg');

        Socialite::shouldReceive('driver->user')->andReturn($abstractUser);

        // 4. Attempt Login
        $response = $this->get('http://univ.elections-hq.test/auth/google/callback');

        // 5. Assert Success
        $response->assertRedirect(route('voter.elections.index'));
        
        $this->assertAuthenticated();
        
        // Assert User created
        $user = User::where('email', 'jane@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Jane Doe', $user->name);
        
        // Assert Linked to OrganizationUser
        $pivot = OrganizationUser::where('organization_id', $org->id)
            ->where('allowed_email', 'jane@example.com')
            ->first();
        $this->assertEquals($user->id, $pivot->user_id);
        $this->assertEquals('active', $pivot->status);
    }

    public function test_voter_cannot_login_to_wrong_organization()
    {
        // Org A (Where user IS allowed)
        $orgA = Organization::factory()->create([
            'subdomain' => 'school-a',
            'status' => 'active'
        ]);
        OrganizationUser::create([
            'organization_id' => $orgA->id,
            'voter_id' => 'A1',
            'allowed_email' => 'student@school-a.edu',
        ]);

        // Org B (Target of attack)
        $orgB = Organization::factory()->create([
            'subdomain' => 'school-b',
            'status' => 'active'
        ]);
        // User NOT in Org B list

        // Mock Google User
        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
        $abstractUser->shouldReceive('getId')->andReturn('google-789');
        $abstractUser->shouldReceive('getName')->andReturn('Student A');
        $abstractUser->shouldReceive('getEmail')->andReturn('student@school-a.edu'); 
        $abstractUser->shouldReceive('getAvatar')->andReturn('pic.jpg');

        Socialite::shouldReceive('driver->user')->andReturn($abstractUser);

        // Attempt Login to Org B
        $response = $this->get('http://school-b.elections-hq.test/auth/google/callback');

        // Assert Fail
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
