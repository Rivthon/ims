<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class MaintenanceHistory extends Model { protected $guarded=[]; protected function casts(): array{return ['performed_at'=>'datetime','checklist_results'=>'array'];} public function performer(){return $this->belongsTo(User::class,'performed_by');} }
