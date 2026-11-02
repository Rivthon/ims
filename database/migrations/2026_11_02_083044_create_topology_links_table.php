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
        Schema::create('topology_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topology_id')->constrained()->cascadeOnDelete();
            $table->foreignId('source_node_id')->constrained('topology_nodes')->cascadeOnDelete();
            $table->foreignId('target_node_id')->constrained('topology_nodes')->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->string('connection_type')->default('ethernet');
            $table->string('source_port')->nullable();
            $table->string('target_port')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->unique(['topology_id', 'source_node_id', 'target_node_id'], 'topology_link_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topology_links');
    }
};
