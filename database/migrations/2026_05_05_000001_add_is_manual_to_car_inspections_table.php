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
        Schema::table('car_inspections', function (Blueprint $table) {
            if (!Schema::hasColumn('car_inspections', 'is_manual')) {
                $table->boolean('is_manual')
                    ->default(false)
                    ->after('status')
                    ->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_inspections', function (Blueprint $table) {
            if (Schema::hasColumn('car_inspections', 'is_manual')) {
                $table->dropIndex(['is_manual']);
                $table->dropColumn('is_manual');
            }
        });
    }
};
