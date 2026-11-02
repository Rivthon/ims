<?php

namespace App\Services;

use App\Models\MonitoredService;
use Illuminate\Support\Facades\Http;
use Throwable;

class ServiceMonitor
{
    public function check(MonitoredService $service): array
    {
        $started = hrtime(true);
        try {
            $response = Http::timeout(12)->withOptions(['allow_redirects' => true])->get($service->url);
            $milliseconds = (int) round((hrtime(true) - $started) / 1_000_000);
            $status = $response->successful() || $response->redirect() ? 'up' : 'down';
            $message = $status === 'up' ? 'Layanan merespons normal.' : 'HTTP '.$response->status();
            $httpCode = $response->status();
        } catch (Throwable $exception) {
            $milliseconds = (int) round((hrtime(true) - $started) / 1_000_000);
            $status = 'down'; $httpCode = null; $message = $exception->getMessage();
        }

        $service->update(['status'=>$status,'response_time_ms'=>$milliseconds,'last_checked_at'=>now()]);
        $service->histories()->create(['status'=>$status,'response_time_ms'=>$milliseconds,'http_code'=>$httpCode,'message'=>$message,'checked_at'=>now()]);
        return compact('status','milliseconds','httpCode','message');
    }
}
