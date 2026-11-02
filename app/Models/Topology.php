<?php

namespace App\Models;

use Database\Factories\TopologyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Topology extends Model
{
    /** @use HasFactory<TopologyFactory> */
    use HasFactory;

    protected $fillable = ['name', 'type', 'description', 'created_by'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function nodes(): HasMany
    {
        return $this->hasMany(TopologyNode::class);
    }

    public function links(): HasMany
    {
        return $this->hasMany(TopologyLink::class);
    }
}
