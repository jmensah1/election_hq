<?php

namespace Database\Factories;

use App\Models\Election;
use App\Models\Organization;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Candidate>
 */
class CandidateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'election_id' => Election::factory(),
            'position_id' => Position::factory(),
            'user_id' => User::factory(),
            'nomination_status' => 'approved',
            'vetting_status' => 'passed',
            'vote_count' => 0,
        ];
    }
}
