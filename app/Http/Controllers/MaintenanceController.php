<?php

namespace App\Http\Controllers;

use App\Models\{Asset, MaintenanceHistory, MaintenanceSchedule, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaintenanceController extends Controller
{
    public function index(Request $request){$items=MaintenanceSchedule::with(['asset','assignee'])->when($request->status,fn($q,$v)=>$q->where('status',$v))->orderBy('scheduled_at')->paginate(15)->withQueryString();return view('maintenance.index',compact('items'));}
    public function create(){return view('maintenance.form',['maintenance'=>new MaintenanceSchedule,'assets'=>Asset::orderBy('name')->get(),'users'=>User::whereIn('role',['super_admin','ict_head','ict_staff'])->get()]);}
    public function store(Request $request){MaintenanceSchedule::create($this->validated($request));return redirect()->route('maintenance.index')->with('success','Jadwal maintenance ditambahkan.');}
    public function show(MaintenanceSchedule $maintenance){$maintenance->load(['asset','assignee','histories.performer']);return view('maintenance.show',compact('maintenance'));}
    public function edit(MaintenanceSchedule $maintenance){return view('maintenance.form',['maintenance'=>$maintenance,'assets'=>Asset::orderBy('name')->get(),'users'=>User::whereIn('role',['super_admin','ict_head','ict_staff'])->get()]);}
    public function update(Request $request,MaintenanceSchedule $maintenance){$maintenance->update($this->validated($request));return redirect()->route('maintenance.show',$maintenance)->with('success','Jadwal diperbarui.');}
    public function complete(Request $request,MaintenanceSchedule $maintenance){$data=$request->validate(['result'=>'required|in:good,needs_repair,failed','notes'=>'nullable|max:5000','checklist_results'=>'nullable|array']);$next=$this->nextDate($maintenance);DB::transaction(function()use($data,$maintenance,$request,$next){MaintenanceHistory::create(['maintenance_schedule_id'=>$maintenance->id,'performed_by'=>$request->user()->id,'performed_at'=>now(),'result'=>$data['result'],'notes'=>$data['notes']??null,'checklist_results'=>$data['checklist_results']??null]);$maintenance->update(['status'=>$data['result']==='good'?'completed':'needs_attention','result_notes'=>$data['notes']??null,'scheduled_at'=>$next,'next_due_at'=>$this->nextDate($maintenance,$next)]);});return back()->with('success','Hasil pemeriksaan tersimpan dan jadwal berikutnya dibuat.');}
    private function validated(Request $r){return $r->validate(['title'=>'required|max:255','asset_id'=>'nullable|exists:assets,id','frequency'=>'required|in:daily,weekly,monthly,semesterly,yearly','scheduled_at'=>'required|date','assigned_to'=>'nullable|exists:users,id','checklist'=>'nullable|max:5000','status'=>'required|in:pending,completed,needs_attention']);}
    private function nextDate(MaintenanceSchedule $m,$from=null){$date=($from?\Carbon\Carbon::parse($from):$m->scheduled_at->copy());return match($m->frequency){'daily'=>$date->addDay(),'weekly'=>$date->addWeek(),'monthly'=>$date->addMonth(),'semesterly'=>$date->addMonths(6),'yearly'=>$date->addYear(),default=>$date->addMonth()};}
}
