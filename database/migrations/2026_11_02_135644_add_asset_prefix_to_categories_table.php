<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('asset_prefix', 12)->nullable()->unique()->after('type');
        });

        DB::table('categories')->where('type', 'asset')->where('name', 'PC Desktop')->update(['asset_prefix' => 'PC']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['asset_prefix']);
            $table->dropColumn('asset_prefix');
        });
    }
};
