<?php
namespace App\Http\Controllers;
use App\Models\{Asset,MaintenanceSchedule,MonitoredService,Ticket};
class DashboardController extends Controller {
 public function __invoke(){
  $ticketCounts=Ticket::selectRaw('status, count(*) total')->groupBy('status')->pluck('total','status');
  return view('dashboard',[
   'ticketCounts'=>$ticketCounts,'assetCount'=>Asset::count(),
   'assetProblems'=>Asset::whereNotIn('condition',['good','new'])->count(),
   'services'=>MonitoredService::orderBy('status')->get(),
   'maintenance'=>MaintenanceSchedule::with('asset')->where('scheduled_at','>=',today())->orderBy('scheduled_at')->limit(5)->get(),
   'recentTickets'=>Ticket::with(['reporter','location'])->latest()->limit(6)->get(),
  ]);
 }
}
