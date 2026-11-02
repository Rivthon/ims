<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Ticket extends Model { protected $guarded=[]; protected function casts(): array{return ['accepted_at'=>'datetime','completed_at'=>'datetime','verified_at'=>'datetime'];} public function reporter(){return $this->belongsTo(User::class,'reporter_id');} public function technician(){return $this->belongsTo(User::class,'technician_id');} public function category(){return $this->belongsTo(Category::class);} public function location(){return $this->belongsTo(Location::class);} public function asset(){return $this->belongsTo(Asset::class);} public function histories(){return $this->hasMany(TicketStatusHistory::class);} }
