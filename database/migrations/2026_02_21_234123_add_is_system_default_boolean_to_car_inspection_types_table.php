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
        Schema::table('car_inspection_types', function (Blueprint $table) {
            $table->boolean('is_system_default')->default(false)->after('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_inspection_types', function (Blueprint $table) {
            $table->dropColumn('is_system_default');
        });
    }
};
