<?php

namespace Database\Factories;

use App\Models\Election;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Position>
 */
class PositionFactory extends Factory
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
            'name' => $this->faker->jobTitle,
            'description' => $this->faker->sentence,
            'display_order' => $this->faker->numberBetween(1, 10),
            'max_candidates' => 5,
            'max_votes' => 1,
            'is_active' => true,
        ];
    }
}
