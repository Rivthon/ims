<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Asset extends Model { protected $guarded=[]; protected function casts(): array{return ['specifications'=>'array','purchase_date'=>'date','warranty_until'=>'date'];} public function location(){return $this->belongsTo(Location::class);} public function category(){return $this->belongsTo(Category::class);} }
