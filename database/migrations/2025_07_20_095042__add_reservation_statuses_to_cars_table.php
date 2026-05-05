<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $statusColumn  = Schema::hasColumn('cars', 'status');
        if ($statusColumn) {
            // Modify the existing status column to include new values
            DB::statement("ALTER TABLE cars MODIFY COLUMN status ENUM('draft', 'published', 'reserved', 'sold', 'inactive') DEFAULT 'draft'");
        } else {
            // If status column doesn't exist, create it
            Schema::table('cars', function (Blueprint $table) {
                $table->enum('status', ['draft', 'published', 'reserved', 'sold', 'inactive'])->default('draft');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        DB::table('cars')
            ->whereIn('status', ['reserved', 'sold'])
            ->update(['status' => 'published']);
        DB::statement("ALTER TABLE cars MODIFY COLUMN status ENUM('draft', 'published', 'inactive') DEFAULT 'draft'");
    }
};
