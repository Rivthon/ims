<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('topology_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topology_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code')->nullable();
            $table->string('name');
            $table->string('device_type');
            $table->string('status')->default('unknown');
            $table->string('ip_address')->nullable();
            $table->string('mac_address')->nullable();
            $table->string('port')->nullable();
            $table->string('provider')->nullable();
            $table->string('ssid')->nullable();
            $table->string('vlan')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->unsignedSmallInteger('channel')->nullable();
            $table->string('resolution')->nullable();
            $table->string('recording_mode')->nullable();
            $table->unsignedSmallInteger('retention_days')->nullable();
            $table->string('connection_type')->nullable();
            $table->decimal('position_x', 8, 2)->default(100);
            $table->decimal('position_y', 8, 2)->default(100);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['topology_id', 'device_type']);
            $table->index(['topology_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topology_nodes');
    }
};
