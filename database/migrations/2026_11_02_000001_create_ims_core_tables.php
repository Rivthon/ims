<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('building')->nullable();
            $table->string('floor')->nullable(); $table->text('description')->nullable(); $table->timestamps();
        });
        Schema::create('categories', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('type')->default('ticket');
            $table->string('color')->default('#2563eb'); $table->timestamps();
        });
        Schema::create('assets', function (Blueprint $table) {
            $table->id(); $table->string('code')->unique(); $table->string('name');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->string('serial_number')->nullable(); $table->string('brand')->nullable();
            $table->string('model')->nullable(); $table->string('pic')->nullable();
            $table->date('purchase_date')->nullable(); $table->date('warranty_until')->nullable();
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->string('condition')->default('good'); $table->string('status')->default('active');
            $table->json('specifications')->nullable(); $table->text('notes')->nullable(); $table->timestamps();
        });
        Schema::create('tickets', function (Blueprint $table) {
            $table->id(); $table->string('number')->unique(); $table->foreignId('reporter_id')->constrained('users');
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('asset_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title'); $table->text('description'); $table->string('priority')->default('medium');
            $table->string('status')->default('reported'); $table->text('resolution')->nullable();
            $table->timestamp('accepted_at')->nullable(); $table->timestamp('completed_at')->nullable();
            $table->timestamp('verified_at')->nullable(); $table->timestamps();
        });
        Schema::create('ticket_status_histories', function (Blueprint $table) {
            $table->id(); $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained(); $table->string('from_status')->nullable();
            $table->string('to_status'); $table->text('notes')->nullable(); $table->timestamps();
        });
        Schema::create('maintenance_schedules', function (Blueprint $table) {
            $table->id(); $table->foreignId('asset_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title'); $table->string('frequency')->default('monthly');
            $table->date('scheduled_at'); $table->date('next_due_at')->nullable();
            $table->string('status')->default('pending'); $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('checklist')->nullable(); $table->text('result_notes')->nullable(); $table->timestamps();
        });
        Schema::create('monitored_services', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('url'); $table->string('type')->default('website');
            $table->string('status')->default('unknown'); $table->unsignedInteger('response_time_ms')->nullable();
            $table->date('ssl_expires_at')->nullable(); $table->date('domain_expires_at')->nullable();
            $table->timestamp('last_checked_at')->nullable(); $table->timestamp('last_backup_at')->nullable();
            $table->boolean('is_active')->default(true); $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitored_services'); Schema::dropIfExists('maintenance_schedules');
        Schema::dropIfExists('ticket_status_histories'); Schema::dropIfExists('tickets');
        Schema::dropIfExists('assets'); Schema::dropIfExists('categories'); Schema::dropIfExists('locations');
    }
};
