<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class LocationControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_ict_staff_can_create_a_location(): void
    {
        $user = User::factory()->create(['role' => 'ict_staff']);

        $response = $this->actingAs($user)->post(route('locations.store'), [
            'name' => 'Lab Komputer 2',
            'building' => 'Gedung Akademik',
            'floor' => '2',
            'description' => 'Sebelah ruang server',
        ]);

        $response->assertRedirect(route('locations.index'));
        $this->assertDatabaseHas('locations', [
            'name' => 'Lab Komputer 2',
            'building' => 'Gedung Akademik',
            'floor' => '2',
        ]);
    }

    public function test_location_name_is_required(): void
    {
        $user = User::factory()->create(['role' => 'ict_staff']);

        $response = $this->actingAs($user)->from(route('locations.create'))->post(route('locations.store'), [
            'building' => 'Gedung Akademik',
        ]);

        $response->assertRedirect(route('locations.create'));
        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('locations', 0);
    }

    public function test_reporter_cannot_manage_locations(): void
    {
        $user = User::factory()->create(['role' => 'reporter']);

        $response = $this->actingAs($user)->post(route('locations.store'), ['name' => 'Lokasi Rahasia']);

        $response->assertForbidden();
        $this->assertDatabaseCount('locations', 0);
    }

    public function test_deleting_location_keeps_asset_and_clears_its_location(): void
    {
        $user = User::factory()->create(['role' => 'ict_head']);
        $location = Location::create(['name' => 'Ruang Lama']);
        $asset = Asset::create(['code' => 'SBH-ICT-PC-900', 'name' => 'PC Lama', 'location_id' => $location->id]);

        $response = $this->actingAs($user)->delete(route('locations.destroy', $location));

        $response->assertRedirect(route('locations.index'));
        $this->assertModelMissing($location);
        $this->assertModelExists($asset);
        $this->assertNull($asset->fresh()->location_id);
    }
}
