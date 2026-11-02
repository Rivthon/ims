<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoringHistory extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['checked_at' => 'datetime'];
    }

    public function monitoredService(): BelongsTo
    {
        return $this->belongsTo(MonitoredService::class);
    }
}
