<?php

namespace App\Models;

use Database\Factories\TopologyLinkFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TopologyLink extends Model
{
    /** @use HasFactory<TopologyLinkFactory> */
    use HasFactory;

    protected $fillable = ['topology_id', 'source_node_id', 'target_node_id', 'label', 'connection_type', 'source_port', 'target_port', 'status'];

    public function topology(): BelongsTo
    {
        return $this->belongsTo(Topology::class);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(TopologyNode::class, 'source_node_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(TopologyNode::class, 'target_node_id');
    }
}
