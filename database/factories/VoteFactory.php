<?php

namespace Database\Factories;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\Organization;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vote>
 */
class VoteFactory extends Factory
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
            'candidate_id' => Candidate::factory(),
        ];
    }
}
