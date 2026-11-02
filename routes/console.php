<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\MonitoredService;
use App\Services\ServiceMonitor;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('monitor:services {--id=}', function (ServiceMonitor $monitor) {
    $query = MonitoredService::where('is_active', true);
    if ($this->option('id')) $query->whereKey($this->option('id'));
    $services = $query->get();
    if ($services->isEmpty()) { $this->warn('Tidak ada layanan aktif.'); return 0; }
    foreach ($services as $service) {
        $result = $monitor->check($service);
        $this->line("{$service->name}: ".strtoupper($result['status'])." ({$result['milliseconds']} ms)");
    }
    return 0;
})->purpose('Memeriksa semua website/server yang aktif');

Schedule::command('monitor:services')->everyFiveMinutes()->withoutOverlapping();
