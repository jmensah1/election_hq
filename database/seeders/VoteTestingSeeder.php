<?php

namespace Database\Seeders;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\Organization;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VoteTestingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orgs = Organization::whereIn('subdomain', ['upsaph', 'test'])->get();

        if ($orgs->isEmpty()) {
            $this->command->info("Organizations 'upsaph' and 'test' not found. Creating them...");
            // Ideally create them or just exit if we expect them to strictly exist. 
            // Let's create them if missing for robustness.
            if (!Organization::where('subdomain', 'upsaph')->exists()) {
                Organization::factory()->create(['name' => 'UPSAPH', 'subdomain' => 'upsaph']);
            }
            if (!Organization::where('subdomain', 'test')->exists()) {
                Organization::factory()->create(['name' => 'Test Org', 'subdomain' => 'test']);
            }
            $orgs = Organization::whereIn('subdomain', ['upsaph', 'test'])->get();
        }

        foreach ($orgs as $org) {
            $this->command->info("Seeding data for organization: {$org->name}");

            // 1. Create a Voting Election
            $election = Election::firstOrCreate(
                [
                    'organization_id' => $org->id,
                    'slug' => 'test-election-' . $org->id,
                ],
                [
                    'title' => 'Student Council Election 2026',
                    'description' => 'Annual election for student council representatives.',
                    'status' => 'voting',
                    'nomination_start_date' => now()->subDays(10),
                    'nomination_end_date' => now()->subDays(5),
                    'vetting_start_date' => now()->subDays(4),
                    'vetting_end_date' => now()->subDays(2),
                    'voting_start_date' => now()->subMinutes(1),
                    'voting_end_date' => now()->addDays(2),
                    'require_photo' => false,
                    'created_by' => User::first()->id ?? 1, // Fallback
                ]
            );

            // Ensure it is in 'voting' status
            $election->update(['status' => 'voting']);

            // 2. Create Positions
            $positions = [];
            $positionNames = ['President', 'General Secretary', 'Treasurer'];
            foreach ($positionNames as $index => $name) {
                $positions[] = Position::firstOrCreate(
                    [
                        'organization_id' => $org->id,
                        'election_id' => $election->id,
                        'name' => $name,
                    ],
                    [
                        'description' => "Role of the {$name}",
                        'display_order' => $index + 1,
                        'max_candidates' => 5,
                        'max_votes' => 1,
                        'is_active' => true,
                    ]
                );
            }

            // 3. Create Candidates
            foreach ($positions as $position) {
                // Create 3 candidates per position
                for ($i = 0; $i < 3; $i++) {
                    $candidateUser = User::factory()->create();
                    
                    // Attach candidate to org
                    $org->users()->attach($candidateUser->id, [
                        'role' => 'voter', // Candidates are also voters usually
                        'status' => 'active',
                        'can_vote' => true,
                        'voter_id' => 'CAND-' . Str::upper(Str::random(6)),
                        'allowed_email' => $candidateUser->email,
                    ]);

                    Candidate::create([
                        'organization_id' => $org->id,
                        'election_id' => $election->id,
                        'position_id' => $position->id,
                        'user_id' => $candidateUser->id,
                        'email' => $candidateUser->email,
                        'candidate_number' => $i + 1,
                        'manifesto' => 'I promise to serve with integrity and dedication.',
                        'nomination_status' => 'approved',
                        'vetting_status' => 'passed',
                        'nominated_at' => now()->subDays(5),
                        'vetted_at' => now()->subDays(3),
                    ]);
                }
            }

            // 4. Create Random Voters
            $voterCount = 20;
            $this->command->info("Creating {$voterCount} voters for {$org->name}...");
            
            $voters = User::factory($voterCount)->create();
            
            foreach ($voters as $voter) {
                // Check if already attached (factory might attach if defined so, but usually not)
                if (!$org->users()->where('user_id', $voter->id)->exists()) {
                    $org->users()->attach($voter->id, [
                        'role' => 'voter',
                        'status' => 'active',
                        'can_vote' => true,
                        'voter_id' => 'VOTE-' . Str::upper(Str::random(8)),
                        'allowed_email' => $voter->email,
                    ]);
                }
            }
        }
    }
}
