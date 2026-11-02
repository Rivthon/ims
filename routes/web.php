<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentRevisionController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportCenterController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TopologyController;
use App\Http\Controllers\TopologyLinkController;
use App\Http\Controllers\TopologyNodeController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'show'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});
Route::middleware(['auth', EnsureUserIsActive::class])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::resource('tickets', TicketController::class)->only(['index', 'create', 'store', 'show']);
    Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.status');
    Route::resource('assets', AssetController::class)->except(['show', 'destroy']);
    Route::resource('locations', LocationController::class)->except('show');
    Route::resource('maintenance', MaintenanceController::class)->except(['destroy']);
    Route::post('/maintenance/{maintenance}/complete', [MaintenanceController::class, 'complete'])->name('maintenance.complete');
    Route::resource('monitoring', MonitoringController::class)->except(['show', 'destroy']);
    Route::post('/monitoring/{monitoring}/check', [MonitoringController::class, 'check'])->name('monitoring.check');
    Route::resource('topologies', TopologyController::class);
    Route::post('/topologies/{topology}/nodes', [TopologyNodeController::class, 'store'])->name('topologies.nodes.store');
    Route::patch('/topologies/{topology}/nodes/{node}', [TopologyNodeController::class, 'update'])->name('topologies.nodes.update');
    Route::get('/topologies/{topology}/nodes/{node}/edit', [TopologyNodeController::class, 'edit'])->name('topologies.nodes.edit');
    Route::put('/topologies/{topology}/nodes/{node}', [TopologyNodeController::class, 'updateDetails'])->name('topologies.nodes.details.update');
    Route::delete('/topologies/{topology}/nodes/{node}', [TopologyNodeController::class, 'destroy'])->name('topologies.nodes.destroy');
    Route::post('/topologies/{topology}/links', [TopologyLinkController::class, 'store'])->name('topologies.links.store');
    Route::delete('/topologies/{topology}/links/{link}', [TopologyLinkController::class, 'destroy'])->name('topologies.links.destroy');
    Route::resource('documents', DocumentController::class);
    Route::post('/documents/{document}/revisions', [DocumentRevisionController::class, 'store'])->name('documents.revisions.store');
    Route::get('/documents/{document}/revisions/{revision}/download', [DocumentRevisionController::class, 'download'])->name('documents.revisions.download');
    Route::get('/reports', [ReportCenterController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportCenterController::class, 'export'])->name('reports.export');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::resource('users', UserController::class)->except('show');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
