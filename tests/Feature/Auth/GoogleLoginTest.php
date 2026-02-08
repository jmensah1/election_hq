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

    public function test_google_names_are_title_cased()
    {
        // 1. Create Organization
        $org = Organization::factory()->create([
            'subdomain' => 'univ-test',
            'status' => 'active'
        ]);

        // 2. Add email to guest list
        OrganizationUser::create([
            'organization_id' => $org->id,
            'voter_id' => '999',
            'allowed_email' => 'caps@example.com',
            'role' => 'voter',
            'status' => 'pending'
        ]);

        // 3. Mock Google User with ALL CAPS name
        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
        $abstractUser->shouldReceive('getId')->andReturn('google-caps');
        $abstractUser->shouldReceive('getName')->andReturn('JOHN DOE'); // ALL CAPS
        $abstractUser->shouldReceive('getEmail')->andReturn('caps@example.com');
        $abstractUser->shouldReceive('getAvatar')->andReturn('pic.jpg');

        Socialite::shouldReceive('driver->user')->andReturn($abstractUser);

        // 4. Attempt Login
        $response = $this->get('http://univ-test.elections-hq.test/auth/google/callback');

        // 5. Assert Success
        $response->assertRedirect(route('voter.elections.index'));
        
        $user = User::where('email', 'caps@example.com')->first();
        $this->assertNotNull($user);
        
        // 6. Assert Name is Title Cased
        $this->assertEquals('John Doe', $user->name);
    }

    public function test_existing_user_name_preserves_local_changes()
    {
        // 1. Create Organization
        $org = Organization::factory()->create([
            'subdomain' => 'univ-test-2',
            'status' => 'active'
        ]);

        // 2. Create User with custom name (simulating Candidate Portal edit)
        $user = User::factory()->create([
            'email' => 'candidate@example.com',
            'name' => 'Jane Candidate', // The name we want to keep
        ]);

        // 3. Add to guest list
        OrganizationUser::create([
            'organization_id' => $org->id,
            'user_id' => $user->id,
            'allowed_email' => 'candidate@example.com',
            'role' => 'voter', 
            'status' => 'active'
        ]);

        // 4. Mock Google User with DIFFERENT name
        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
        $abstractUser->shouldReceive('getId')->andReturn('google-999');
        $abstractUser->shouldReceive('getName')->andReturn('JANE DOE'); // Google's name (different)
        $abstractUser->shouldReceive('getEmail')->andReturn('candidate@example.com');
        $abstractUser->shouldReceive('getAvatar')->andReturn('pic.jpg');

        Socialite::shouldReceive('driver->user')->andReturn($abstractUser);

        // 5. Attempt Login
        $response = $this->get('http://univ-test-2.elections-hq.test/auth/google/callback');

        // 6. Assert Success
        $response->assertRedirect(route('voter.elections.index'));
        
        // 7. Assert Name is UNCHANGED
        $user->refresh();
        $this->assertEquals('Jane Candidate', $user->name);
        $this->assertNotEquals('Jane Doe', $user->name);
        $this->assertNotEquals('JANE DOE', $user->name);
    }
}
