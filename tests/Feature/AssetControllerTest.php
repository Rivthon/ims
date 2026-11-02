<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AssetControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_it_continues_numbering_from_existing_asset_code(): void
    {
        $user = User::factory()->create(['role' => 'ict_staff']);
        $category = Category::create(['name' => 'PC Desktop', 'type' => 'asset', 'asset_prefix' => 'PC']);
        Asset::create(['code' => 'SBH-ICT-PC-001', 'name' => 'PC Lama']);

        $response = $this->actingAs($user)->post(route('assets.store'), $this->validData($category));

        $response->assertRedirect(route('assets.index'));
        $this->assertDatabaseHas('assets', ['code' => 'SBH-ICT-PC-002', 'name' => 'PC Baru']);
    }

    public function test_each_asset_category_has_an_independent_sequence(): void
    {
        $user = User::factory()->create(['role' => 'ict_head']);
        $category = Category::create(['name' => 'Laptop', 'type' => 'asset', 'asset_prefix' => 'LP']);

        $response = $this->actingAs($user)->post(route('assets.store'), $this->validData($category));

        $response->assertRedirect(route('assets.index'));
        $this->assertDatabaseHas('assets', ['code' => 'SBH-ICT-LP-001']);
    }

    public function test_submitted_asset_code_is_ignored(): void
    {
        $user = User::factory()->create(['role' => 'ict_staff']);
        $category = Category::create(['name' => 'Printer', 'type' => 'asset', 'asset_prefix' => 'PRIN']);
        $data = $this->validData($category) + ['code' => 'KODE-BEBAS'];

        $response = $this->actingAs($user)->post(route('assets.store'), $data);

        $response->assertRedirect(route('assets.index'));
        $this->assertDatabaseHas('assets', ['code' => 'SBH-ICT-PRIN-001']);
        $this->assertDatabaseMissing('assets', ['code' => 'KODE-BEBAS']);
    }

    public function test_reporter_cannot_create_an_asset(): void
    {
        $user = User::factory()->create(['role' => 'reporter']);
        $category = Category::create(['name' => 'Proyektor', 'type' => 'asset', 'asset_prefix' => 'PROJ']);

        $response = $this->actingAs($user)->post(route('assets.store'), $this->validData($category));

        $response->assertForbidden();
        $this->assertDatabaseCount('assets', 0);
    }

    /** @return array<string, mixed> */
    private function validData(Category $category): array
    {
        return [
            'name' => 'PC Baru',
            'category_id' => $category->id,
            'condition' => 'good',
            'status' => 'active',
        ];
    }
}
