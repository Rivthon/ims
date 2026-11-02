<?php

namespace Database\Factories;

use App\Models\Topology;
use App\Models\TopologyLink;
use App\Models\TopologyNode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TopologyLink>
 */
class TopologyLinkFactory extends Factory
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
            'source_node_id' => fn (array $attributes) => TopologyNode::factory()->create(['topology_id' => $attributes['topology_id']])->id,
            'target_node_id' => fn (array $attributes) => TopologyNode::factory()->create(['topology_id' => $attributes['topology_id']])->id,
            'connection_type' => 'ethernet',
            'status' => 'active',
        ];
    }
}
