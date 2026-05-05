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
        Schema::table('cars', function (Blueprint $table) {
            // Add auction_status field to track auction-related status
            $table->enum('auction_status', [
                'not_in_auction',
                'requested_for_auction', 
                'approved_for_auction',
                'in_auction',
                'auction_ended',
                'auction_won',
                'auction_cancelled'
            ])->default('not_in_auction')->after('status');
            
            // Add current auction ID reference (nullable) - foreign key will be added later
            $table->unsignedBigInteger('current_auction_id')->nullable()->after('auction_status');
            
            // Add index for auction status queries
            $table->index('auction_status');
            $table->index(['status', 'auction_status']);
        });

        // Update the existing status enum to include auction-related statuses
        $statusColumn = Schema::hasColumn('cars', 'status');
        if ($statusColumn) {
            // Modify the existing status column to include auction status
            DB::statement("ALTER TABLE cars MODIFY COLUMN status ENUM('draft', 'published', 'reserved', 'sold', 'inactive', 'in_auction') DEFAULT 'draft'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            // Drop the added columns
            $table->dropColumn(['auction_status', 'current_auction_id']);
        });

        // Revert the status enum to previous values
        DB::statement("ALTER TABLE cars MODIFY COLUMN status ENUM('draft', 'published', 'reserved', 'sold', 'inactive') DEFAULT 'draft'");
    }
};