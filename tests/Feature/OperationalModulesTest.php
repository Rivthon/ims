<?php

namespace Tests\Feature;

use App\Models\{Asset, MaintenanceSchedule, MonitoredService, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_maintenance_schedule(): void
    {
        $user = User::factory()->create(['role'=>'ict_staff']);
        $asset = Asset::create(['code'=>'TEST-001','name'=>'Test Asset','condition'=>'good','status'=>'active']);
        $response = $this->actingAs($user)->post(route('maintenance.store'),[
            'title'=>'Pemeriksaan Test','asset_id'=>$asset->id,'frequency'=>'monthly',
            'scheduled_at'=>now()->addDay()->format('Y-m-d'),'assigned_to'=>$user->id,
            'status'=>'pending','checklist'=>"Cek daya\nCek koneksi",
        ]);
        $response->assertRedirect(route('maintenance.index'));
        $this->assertDatabaseHas('maintenance_schedules',['title'=>'Pemeriksaan Test']);
    }

    public function test_monitoring_page_lists_registered_service(): void
    {
        $user = User::factory()->create(['role'=>'ict_staff']);
        MonitoredService::create(['name'=>'Portal Test','url'=>'https://example.com','status'=>'unknown']);
        $this->actingAs($user)->get(route('monitoring.index'))->assertOk()->assertSee('Portal Test');
    }
}
