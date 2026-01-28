<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\OrganizationUser;
use Illuminate\Support\Facades\DB;

class VoterSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure Organization Exists
        $org = Organization::firstOrCreate(
            ['slug' => 'test-organization'],
            [
                'name' => 'Test Organization',
                'subdomain' => 'test',
                'status' => 'active',
                'timezone' => 'Africa/Accra'
            ]
        );

        // 2. Add User to Guest List
        OrganizationUser::updateOrCreate(
            [
                'organization_id' => $org->id,
                'allowed_email' => 'elevatedbaffoe@gmail.com'
            ],
            [
                'voter_id' => 'TEST-' . rand(1000, 9999),
                'role' => 'voter',
                'status' => 'active',
                'can_vote' => true
            ]
        );

        $this->command->info('seeded: Test Organization and Voter (elevatedbaffoe@gmail.com)');
    }
}
