<?php

namespace App\Models;

use Database\Factories\TopologyNodeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TopologyNode extends Model
{
    /** @use HasFactory<TopologyNodeFactory> */
    use HasFactory;

    protected $fillable = ['topology_id', 'asset_id', 'location_id', 'code', 'name', 'device_type', 'status', 'ip_address', 'mac_address', 'port', 'provider', 'ssid', 'vlan', 'model', 'serial_number', 'channel', 'resolution', 'recording_mode', 'retention_days', 'connection_type', 'position_x', 'position_y', 'notes'];

    protected function casts(): array
    {
        return ['position_x' => 'float', 'position_y' => 'float'];
    }

    public function topology(): BelongsTo
    {
        return $this->belongsTo(Topology::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function outgoingLinks(): HasMany
    {
        return $this->hasMany(TopologyLink::class, 'source_node_id');
    }

    public function incomingLinks(): HasMany
    {
        return $this->hasMany(TopologyLink::class, 'target_node_id');
    }
}
