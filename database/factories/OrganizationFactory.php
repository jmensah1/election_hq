<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Organization>
 */
class OrganizationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->name . ' Organization';
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'subdomain' => Str::slug($name),
            'custom_domain' =>  null,
            'timezone' => 'UTC',
            'status' => 'active',
            'subscription_plan' => 'free',
            'sms_enabled' => false,
            'max_voters' => 100,
        ];
    }
}
