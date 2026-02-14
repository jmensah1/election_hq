<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OrganizationSetupService
{
    public function __construct(
        protected PlanLimitService $planLimitService
    ) {}

    /**
     * Create an organization and set up the admin user.
     *
     * @param Lead $lead
     * @param array $data Validated data from the request
     * @param string|null $logoPath
     * @return Organization
     */
    public function createOrganization(Lead $lead, array $data, ?string $logoPath = null): Organization
    {
        // Get plan limits
        $planLimits = $this->planLimitService->getPlanLimits($lead->plan_tier);

        // Calculate subscription expiry
        $expiresAt = $lead->billing_cycle === 'annual' 
            ? now()->addYear() 
            : now()->addMonth();

        // Create organization
        $organization = Organization::create([
            'name' => $data['organization_name'],
            'slug' => $this->generateUniqueSlug($data['organization_name']),
            'subdomain' => strtolower($data['subdomain']),
            'timezone' => $data['timezone'],
            'logo_path' => $logoPath,
            'status' => 'active',
            'subscription_plan' => $lead->plan_tier,
            'subscription_expires_at' => $expiresAt,
            'max_voters' => $planLimits['max_voters'] ?? 100,
            'sms_enabled' => $planLimits['sms_enabled'] ?? false,
        ]);

        // Find or create admin user
        $user = User::where('email', $lead->email)->first();
        
        if (!$user) {
            $user = User::create([
                'name' => $data['admin_name'],
                'email' => $lead->email,
                'phone' => $lead->phone,
                'password' => Hash::make($data['password']),
            ]);
        } else {
            // Update existing user's password/name if they are claiming this org
            $user->update([
                'name' => $data['admin_name'],
                'password' => Hash::make($data['password']),
            ]);
        }

        // Attach user as admin
        $canVote = !empty($data['will_vote']) && $data['will_vote'] == 1;
        $voterId = $canVote ? ($data['voter_id'] ?? null) : null;

        $organization->users()->attach($user->id, [
            'role' => 'admin',
            'status' => 'active',
            'can_vote' => $canVote,
            'voter_id' => $voterId,
            'allowed_email' => $user->email,
        ]);

        return $organization;
    }
    /**
     * Generate a unique slug for the organization.
     */
    protected function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while (Organization::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }
}
