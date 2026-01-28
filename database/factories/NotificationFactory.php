<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
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
            'user_id' => User::factory(),
            'type' => 'email',
            'category' => 'vote_confirmation',
            'recipient' => $this->faker->email,
            'subject' => $this->faker->sentence,
            'message' => $this->faker->paragraph,
            'status' => 'sent',
            'sent_at' => now(),
        ];
    }
}
