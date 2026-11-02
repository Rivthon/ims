<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class MonitoredService extends Model { protected $guarded=[]; protected function casts(): array{return ['ssl_expires_at'=>'date','domain_expires_at'=>'date','last_checked_at'=>'datetime','last_backup_at'=>'datetime','is_active'=>'boolean'];} public function histories(){return $this->hasMany(MonitoringHistory::class);} }
