<?php

namespace Database\Factories;

use App\Models\Topology;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Topology>
 */
class TopologyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'type' => fake()->randomElement(['network', 'cctv']),
            'description' => fake()->sentence(),
            'created_by' => User::factory(),
        ];
    }
}
