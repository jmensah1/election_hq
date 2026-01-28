<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Election>
 */
class ElectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence(3);
        $startDate = $this->faker->dateTimeBetween('now', '+1 month');
        $endDate = (clone $startDate)->modify('+2 days');

        return [
            'organization_id' => Organization::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => $this->faker->paragraph,
            'nomination_start_date' => (clone $startDate)->modify('-2 weeks'),
            'nomination_end_date' => (clone $startDate)->modify('-1 week'),
            'vetting_start_date' => (clone $startDate)->modify('-6 days'),
            'vetting_end_date' => (clone $startDate)->modify('-3 days'),
            'voting_start_date' => $startDate,
            'voting_end_date' => $endDate,
            'status' => 'draft',
            'created_by' => User::factory(),
            'require_photo' => true,
        ];
    }
}
