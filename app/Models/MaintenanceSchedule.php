<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class MaintenanceSchedule extends Model { protected $guarded=[]; protected function casts(): array{return ['scheduled_at'=>'date','next_due_at'=>'date'];} public function asset(){return $this->belongsTo(Asset::class);} public function assignee(){return $this->belongsTo(User::class,'assigned_to');} public function histories(){return $this->hasMany(MaintenanceHistory::class);} }
