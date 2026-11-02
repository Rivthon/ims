<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('maintenance_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('performed_by')->constrained('users');
            $table->dateTime('performed_at');
            $table->string('result');
            $table->text('notes')->nullable();
            $table->json('checklist_results')->nullable();
            $table->timestamps();
        });

        Schema::create('monitoring_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitored_service_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->unsignedSmallInteger('http_code')->nullable();
            $table->text('message')->nullable();
            $table->timestamp('checked_at');
            $table->timestamps();
            $table->index(['monitored_service_id', 'checked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitoring_histories');
        Schema::dropIfExists('maintenance_histories');
    }
};
