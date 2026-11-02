<?php

use App\Http\Controllers\{AssetController,AuthController,DashboardController,MaintenanceController,MonitoringController,TicketController};
use Illuminate\Support\Facades\Route;

Route::redirect('/','/dashboard');
Route::middleware('guest')->group(function(){Route::get('/login',[AuthController::class,'show'])->name('login');Route::post('/login',[AuthController::class,'login']);});
Route::middleware('auth')->group(function(){
 Route::get('/dashboard',DashboardController::class)->name('dashboard');
 Route::resource('tickets',TicketController::class)->only(['index','create','store','show']);
 Route::patch('/tickets/{ticket}/status',[TicketController::class,'updateStatus'])->name('tickets.status');
 Route::resource('assets',AssetController::class)->except(['show','destroy']);
 Route::resource('maintenance',MaintenanceController::class)->except(['destroy']);
 Route::post('/maintenance/{maintenance}/complete',[MaintenanceController::class,'complete'])->name('maintenance.complete');
 Route::resource('monitoring',MonitoringController::class)->except(['show','destroy']);
 Route::post('/monitoring/{monitoring}/check',[MonitoringController::class,'check'])->name('monitoring.check');
 Route::post('/logout',[AuthController::class,'logout'])->name('logout');
});
