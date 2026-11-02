<?php

namespace App\Http\Controllers;

use App\Models\MonitoredService;
use App\Services\ServiceMonitor;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    public function index(){return view('monitoring.index',['services'=>MonitoredService::with(['histories'=>fn($q)=>$q->latest('checked_at')->limit(10)])->orderBy('name')->get()]);}
    public function create(){return view('monitoring.form',['service'=>new MonitoredService]);}
    public function store(Request $request){MonitoredService::create($this->validated($request));return redirect()->route('monitoring.index')->with('success','Layanan monitoring ditambahkan.');}
    public function edit(MonitoredService $monitoring){return view('monitoring.form',['service'=>$monitoring]);}
    public function update(Request $request,MonitoredService $monitoring){$monitoring->update($this->validated($request));return redirect()->route('monitoring.index')->with('success','Layanan diperbarui.');}
    public function check(MonitoredService $monitoring,ServiceMonitor $monitor){$result=$monitor->check($monitoring);return back()->with('success',"Pengecekan selesai: ".strtoupper($result['status'])." ({$result['milliseconds']} ms).");}
    private function validated(Request $r){$data=$r->validate(['name'=>'required|max:255','url'=>'required|url|max:500','type'=>'required|in:website,server,api,application','ssl_expires_at'=>'nullable|date','domain_expires_at'=>'nullable|date','last_backup_at'=>'nullable|date','is_active'=>'nullable|boolean']);$data['is_active']=$r->boolean('is_active');return $data;}
}
