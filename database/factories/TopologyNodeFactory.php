<?php

namespace Database\Factories;

use App\Models\Topology;
use App\Models\TopologyNode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TopologyNode>
 */
class TopologyNodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'topology_id' => Topology::factory(),
            'name' => fake()->words(2, true),
            'device_type' => fake()->randomElement(['router', 'switch', 'access_point', 'camera']),
            'status' => fake()->randomElement(['online', 'offline', 'unknown']),
            'ip_address' => fake()->ipv4(),
            'position_x' => fake()->numberBetween(80, 900),
            'position_y' => fake()->numberBetween(80, 500),
        ];
    }
}
