<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Location;
use App\Models\MaintenanceSchedule;
use App\Models\MonitoredService;
use App\Models\Ticket;
use App\Models\TicketStatusHistory;
use App\Models\Topology;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(['email' => 'admin@ims.local'], ['name' => 'Administrator IMS', 'password' => 'password', 'role' => 'super_admin', 'unit' => 'ICT']);
        $staff = User::firstOrCreate(['email' => 'staff@ims.local'], ['name' => 'Staff ICT', 'password' => 'password', 'role' => 'ict_staff', 'unit' => 'ICT']);
        $ict = Location::firstOrCreate(['name' => 'Ruang ICT'], ['building' => 'Gedung Utama', 'floor' => '2']);
        $lab = Location::firstOrCreate(['name' => 'Lab Komputer'], ['building' => 'Gedung Akademik', 'floor' => '1']);
        foreach (['Komputer', 'Printer', 'Jaringan', 'Software', 'CCTV'] as $name) {
            Category::firstOrCreate(['name' => $name, 'type' => 'ticket']);
        }
        $assetCategory = Category::firstOrCreate(['name' => 'PC Desktop', 'type' => 'asset']);
        $asset = Asset::firstOrCreate(['code' => 'AST-PC-0001'], ['name' => 'PC Staff ICT', 'category_id' => $assetCategory->id, 'location_id' => $ict->id, 'brand' => 'Dell', 'condition' => 'good', 'status' => 'active', 'pic' => 'Staff ICT']);
        $ticket = Ticket::firstOrCreate(['number' => 'TKT-'.now()->format('Ym').'-00001'], ['reporter_id' => $admin->id, 'technician_id' => $staff->id, 'category_id' => Category::where('name', 'Jaringan')->first()->id, 'location_id' => $lab->id, 'title' => 'Koneksi internet lab tidak stabil', 'description' => 'Beberapa komputer mengalami putus koneksi saat digunakan bersamaan.', 'priority' => 'high', 'status' => 'in_progress']);
        TicketStatusHistory::firstOrCreate(['ticket_id' => $ticket->id, 'to_status' => 'reported'], ['user_id' => $admin->id, 'notes' => 'Laporan awal']);
        MaintenanceSchedule::firstOrCreate(['title' => 'Pemeriksaan PC Lab Bulanan', 'scheduled_at' => today()->addDays(5)], ['asset_id' => $asset->id, 'frequency' => 'monthly', 'next_due_at' => today()->addMonth(), 'assigned_to' => $staff->id]);
        MonitoredService::firstOrCreate(['name' => 'Website Utama'], ['url' => 'https://example.edu', 'status' => 'up', 'response_time_ms' => 184, 'last_checked_at' => now()]);

        $network = Topology::firstOrCreate(['name' => 'Jaringan Gedung Utama'], ['type' => 'network', 'description' => 'Internet → Router → Core Switch → Access Point dan Server', 'created_by' => $admin->id]);
        $internet = $network->nodes()->firstOrCreate(['name' => 'Internet Provider'], ['device_type' => 'internet', 'status' => 'online', 'provider' => 'ISP Utama', 'position_x' => 130, 'position_y' => 130]);
        $router = $network->nodes()->firstOrCreate(['name' => 'Router Utama'], ['device_type' => 'router', 'status' => 'online', 'ip_address' => '192.168.1.1', 'position_x' => 390, 'position_y' => 130]);
        $core = $network->nodes()->firstOrCreate(['name' => 'Core Switch'], ['device_type' => 'core_switch', 'status' => 'online', 'ip_address' => '192.168.1.2', 'vlan' => '10,20,30', 'position_x' => 650, 'position_y' => 130]);
        $network->links()->firstOrCreate(['source_node_id' => $internet->id, 'target_node_id' => $router->id], ['connection_type' => 'fiber', 'label' => 'WAN', 'status' => 'active']);
        $network->links()->firstOrCreate(['source_node_id' => $router->id, 'target_node_id' => $core->id], ['connection_type' => 'ethernet', 'label' => 'LAN 1 Gbps', 'status' => 'active']);

        $cctv = Topology::firstOrCreate(['name' => 'CCTV Gedung Utama'], ['type' => 'cctv', 'description' => 'Router → PoE Switch → NVR dan kamera IP', 'created_by' => $admin->id]);
        $cctvRouter = $cctv->nodes()->firstOrCreate(['name' => 'Router CCTV'], ['device_type' => 'router', 'status' => 'online', 'ip_address' => '192.168.20.1', 'position_x' => 180, 'position_y' => 180]);
        $poe = $cctv->nodes()->firstOrCreate(['name' => 'PoE Switch'], ['device_type' => 'poe_switch', 'status' => 'online', 'ip_address' => '192.168.20.2', 'position_x' => 480, 'position_y' => 180]);
        $camera = $cctv->nodes()->firstOrCreate(['name' => 'Kamera Lobby'], ['device_type' => 'camera', 'status' => 'online', 'ip_address' => '192.168.20.11', 'location_id' => $ict->id, 'channel' => 1, 'resolution' => '1080p', 'recording_mode' => 'Continuous', 'retention_days' => 30, 'position_x' => 780, 'position_y' => 180]);
        $cctv->links()->firstOrCreate(['source_node_id' => $cctvRouter->id, 'target_node_id' => $poe->id], ['connection_type' => 'ethernet', 'status' => 'active']);
        $cctv->links()->firstOrCreate(['source_node_id' => $poe->id, 'target_node_id' => $camera->id], ['connection_type' => 'poe', 'label' => 'Port 1', 'status' => 'active']);
    }
}
