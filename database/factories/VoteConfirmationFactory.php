<?php

namespace Database\Factories;

use App\Models\Election;
use App\Models\Organization;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VoteConfirmation>
 */
class VoteConfirmationFactory extends Factory
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
            'voted_at' => now(),
            'ip_address' => $this->faker->ipv4,
            'user_agent' => $this->faker->userAgent,
        ];
    }
}
