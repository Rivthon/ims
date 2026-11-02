<?php

namespace Database\Seeders;

use App\Models\{Asset, Category, Location, MaintenanceSchedule, MonitoredService, Ticket, TicketStatusHistory, User};
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
        $admin=User::firstOrCreate(['email'=>'admin@ims.local'],['name'=>'Administrator IMS','password'=>'password','role'=>'super_admin','unit'=>'ICT']);
        $staff=User::firstOrCreate(['email'=>'staff@ims.local'],['name'=>'Staff ICT','password'=>'password','role'=>'ict_staff','unit'=>'ICT']);
        $ict=Location::firstOrCreate(['name'=>'Ruang ICT'],['building'=>'Gedung Utama','floor'=>'2']);
        $lab=Location::firstOrCreate(['name'=>'Lab Komputer'],['building'=>'Gedung Akademik','floor'=>'1']);
        foreach(['Komputer','Printer','Jaringan','Software','CCTV'] as $name) Category::firstOrCreate(['name'=>$name,'type'=>'ticket']);
        $assetCategory=Category::firstOrCreate(['name'=>'PC Desktop','type'=>'asset']);
        $asset=Asset::firstOrCreate(['code'=>'AST-PC-0001'],['name'=>'PC Staff ICT','category_id'=>$assetCategory->id,'location_id'=>$ict->id,'brand'=>'Dell','condition'=>'good','status'=>'active','pic'=>'Staff ICT']);
        $ticket=Ticket::firstOrCreate(['number'=>'TKT-'.now()->format('Ym').'-00001'],['reporter_id'=>$admin->id,'technician_id'=>$staff->id,'category_id'=>Category::where('name','Jaringan')->first()->id,'location_id'=>$lab->id,'title'=>'Koneksi internet lab tidak stabil','description'=>'Beberapa komputer mengalami putus koneksi saat digunakan bersamaan.','priority'=>'high','status'=>'in_progress']);
        TicketStatusHistory::firstOrCreate(['ticket_id'=>$ticket->id,'to_status'=>'reported'],['user_id'=>$admin->id,'notes'=>'Laporan awal']);
        MaintenanceSchedule::firstOrCreate(['title'=>'Pemeriksaan PC Lab Bulanan','scheduled_at'=>today()->addDays(5)],['asset_id'=>$asset->id,'frequency'=>'monthly','next_due_at'=>today()->addMonth(),'assigned_to'=>$staff->id]);
        MonitoredService::firstOrCreate(['name'=>'Website Utama'],['url'=>'https://example.edu','status'=>'up','response_time_ms'=>184,'last_checked_at'=>now()]);
    }
}
