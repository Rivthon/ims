<?php

namespace Tests\Feature;

use App\Models\Topology;
use App\Models\TopologyNode;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class TopologyControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $this->get(route('topologies.index'))->assertRedirect(route('login'));
    }

    public function test_reporter_can_view_topology_but_cannot_create_one(): void
    {
        $reporter = User::factory()->create(['role' => 'reporter']);
        $topology = Topology::factory()->create(['created_by' => $reporter->id]);

        $this->actingAs($reporter)->get(route('topologies.show', $topology))->assertOk()->assertSee($topology->name);
        $this->actingAs($reporter)->post(route('topologies.store'), ['name' => 'Restricted', 'type' => 'network'])->assertForbidden();

        $this->assertDatabaseMissing('topologies', ['name' => 'Restricted']);
    }

    public function test_ict_staff_can_create_network_topology(): void
    {
        $staff = User::factory()->create(['role' => 'ict_staff']);

        $response = $this->actingAs($staff)->post(route('topologies.store'), [
            'name' => 'Jaringan Kampus',
            'type' => 'network',
            'description' => 'Topologi inti kampus.',
        ]);

        $topology = Topology::query()->where('name', 'Jaringan Kampus')->firstOrFail();
        $response->assertRedirect(route('topologies.show', $topology));
        $this->assertSame($staff->id, $topology->created_by);
    }

    public function test_node_position_can_be_saved_from_visual_canvas(): void
    {
        $staff = User::factory()->create(['role' => 'ict_staff']);
        $topology = Topology::factory()->create(['created_by' => $staff->id]);
        $node = TopologyNode::factory()->create(['topology_id' => $topology->id, 'position_x' => 100, 'position_y' => 100]);

        $response = $this->actingAs($staff)->patchJson(route('topologies.nodes.update', [$topology, $node]), [
            'position_x' => 420.5,
            'position_y' => 255.25,
        ]);

        $response->assertOk()->assertJson(['message' => 'Posisi tersimpan.']);
        $this->assertDatabaseHas('topology_nodes', ['id' => $node->id, 'position_x' => 420.5, 'position_y' => 255.25]);
    }

    public function test_ict_staff_can_edit_node_details_without_changing_canvas_position(): void
    {
        $staff = User::factory()->create(['role' => 'ict_staff']);
        $topology = Topology::factory()->create(['created_by' => $staff->id, 'type' => 'network']);
        $node = TopologyNode::factory()->create(['topology_id' => $topology->id, 'name' => 'Switch Lama', 'position_x' => 310, 'position_y' => 220]);

        $response = $this->actingAs($staff)->put(route('topologies.nodes.details.update', [$topology, $node]), [
            'name' => 'Core Switch Utama',
            'device_type' => 'core_switch',
            'status' => 'online',
            'ip_address' => '192.168.10.2',
            'mac_address' => 'AA:BB:CC:DD:EE:FF',
            'vlan' => '10,20,30',
        ]);

        $response->assertRedirect(route('topologies.show', $topology));
        $node->refresh();
        $this->assertSame('Core Switch Utama', $node->name);
        $this->assertSame('192.168.10.2', $node->ip_address);
        $this->assertSame(310.0, $node->position_x);
        $this->assertSame(220.0, $node->position_y);
    }

    public function test_ict_staff_can_delete_node_and_its_connections(): void
    {
        $staff = User::factory()->create(['role' => 'ict_staff']);
        $topology = Topology::factory()->create(['created_by' => $staff->id]);
        $source = TopologyNode::factory()->create(['topology_id' => $topology->id]);
        $target = TopologyNode::factory()->create(['topology_id' => $topology->id]);
        $link = $topology->links()->create([
            'source_node_id' => $source->id,
            'target_node_id' => $target->id,
            'connection_type' => 'ethernet',
            'status' => 'active',
        ]);

        $response = $this->actingAs($staff)->delete(route('topologies.nodes.destroy', [$topology, $source]));

        $response->assertRedirect();
        $this->assertModelMissing($source);
        $this->assertModelMissing($link);
        $this->assertModelExists($target);
    }

    public function test_reporter_cannot_delete_topology_node(): void
    {
        $reporter = User::factory()->create(['role' => 'reporter']);
        $topology = Topology::factory()->create(['created_by' => $reporter->id]);
        $node = TopologyNode::factory()->create(['topology_id' => $topology->id]);

        $response = $this->actingAs($reporter)->delete(route('topologies.nodes.destroy', [$topology, $node]));

        $response->assertForbidden();
        $this->assertModelExists($node);
    }

    public function test_connection_rejects_node_from_another_topology(): void
    {
        $staff = User::factory()->create(['role' => 'ict_staff']);
        $firstTopology = Topology::factory()->create(['created_by' => $staff->id]);
        $secondTopology = Topology::factory()->create(['created_by' => $staff->id]);
        $source = TopologyNode::factory()->create(['topology_id' => $firstTopology->id]);
        $foreignTarget = TopologyNode::factory()->create(['topology_id' => $secondTopology->id]);

        $response = $this->actingAs($staff)->from(route('topologies.show', $firstTopology))->post(route('topologies.links.store', $firstTopology), [
            'source_node_id' => $source->id,
            'target_node_id' => $foreignTarget->id,
            'connection_type' => 'ethernet',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('topologies.show', $firstTopology))->assertSessionHasErrors('source_node_id');
        $this->assertDatabaseCount('topology_links', 0);
    }
}
