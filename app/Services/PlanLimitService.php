<?php

namespace App\Services;

use App\Models\Organization;

class PlanLimitService
{
    /**
     * Plan definitions and their limits.
     * -1 indicates unlimited.
     */
    public const PLANS = [
        'free' => [
            'name' => 'Free',
            'max_voters' => 50,
            'max_elections' => 1,
            'storage_limit_mb' => 100,
            'custom_domain' => false,
            'sms_enabled' => false,
            'audit_log_retention_days' => 7,
            'remove_branding' => false,
        ],
        'basic' => [
            'name' => 'Basic',
            'max_voters' => 500,
            'max_elections' => 3,
            'storage_limit_mb' => 1024,
            'custom_domain' => true,
            'sms_enabled' => false,
            'audit_log_retention_days' => 30,
            'remove_branding' => false,
        ],
        'premium' => [
            'name' => 'Premium',
            'max_voters' => 2000,
            'max_elections' => -1,
            'storage_limit_mb' => 10240,
            'custom_domain' => true,
            'sms_enabled' => true,
            'audit_log_retention_days' => 365,
            'remove_branding' => true,
        ],
        'enterprise' => [
            'name' => 'Enterprise',
            'max_voters' => -1,
            'max_elections' => -1,
            'storage_limit_mb' => -1,
            'custom_domain' => true,
            'sms_enabled' => true,
            'audit_log_retention_days' => -1,
            'remove_branding' => true,
        ],
    ];

    /**
     * Get the limits for a specific plan.
     */
    public function getPlanLimits(string $plan): array
    {
        return self::PLANS[$plan] ?? self::PLANS['free'];
    }

    /**
     * Check if the organization can add more voters.
     */
    public function canAddVoter(Organization $organization): bool
    {
        $limits = $this->getPlanLimits($organization->subscription_plan);
        
        if ($limits['max_voters'] === -1) {
            return true;
        }

        // Count current voters (users with 'voter' role in the pivot)
        // Adjust this query based on how voters are exactly stored if needed
        $currentCount = $organization->users()
            ->wherePivot('role', 'voter')
            ->count();

        return $currentCount < $limits['max_voters'];
    }

    /**
     * Check if the organization can create another active election.
     */
    public function canCreateElection(Organization $organization): bool
    {
        $limits = $this->getPlanLimits($organization->subscription_plan);

        if ($limits['max_elections'] === -1) {
            return true;
        }

        // Count active elections (status not 'completed' or 'cancelled')
        $currentCount = $organization->elections()
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        return $currentCount < $limits['max_elections'];
    }

    /**
     * Check if the organization is allowed to use Custom Domains.
     */
    public function canUseCustomDomain(Organization $organization): bool
    {
        $limits = $this->getPlanLimits($organization->subscription_plan);
        return $limits['custom_domain'] ?? false;
    }

    /**
     * Check if the organization is allowed to use SMS.
     */
    public function canUseSMS(Organization $organization): bool
    {
        $limits = $this->getPlanLimits($organization->subscription_plan);
        return $limits['sms_enabled'] ?? false;
    }

    /**
     * Get the message explaining the limit.
     */
    public function getLimitMessage(string $feature, Organization $organization): string
    {
        $planName = self::PLANS[$organization->subscription_plan]['name'] ?? 'Current';
        $limits = $this->getPlanLimits($organization->subscription_plan);

        return match ($feature) {
            'voters' => "The {$planName} plan is limited to {$limits['max_voters']} voters. Please upgrade to add more.",
            'elections' => "The {$planName} plan is limited to {$limits['max_elections']} active elections. Please upgrade or complete existing elections.",
            'custom_domain' => "Custom domains are not available on the {$planName} plan. Please upgrade to Basic or higher.",
            'sms' => "SMS notifications are not available on the {$planName} plan. Please upgrade to Premium or higher.",
            default => "This feature is not available on your current plan.",
        };
    }
}
