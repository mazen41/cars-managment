<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Enums\CarModerationStatusEnum;
use App\Enums\CarStatusEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            // Add new fields
            $table->string('moderation_status')->default(CarModerationStatusEnum::PENDING)->after('status');
            $table->string('car_status')->default(CarStatusEnum::AVAILABLE)->after('moderation_status');
        });

        // Migrate existing data
        DB::statement("
            UPDATE cars SET 
                moderation_status = CASE 
                    WHEN status IN ('published') THEN 'published'
                    WHEN status IN ('draft') THEN 'pending'
                    ELSE 'pending'
                END,
                car_status = CASE 
                    WHEN status IN ('reserved') THEN 'reserved'
                    WHEN status IN ('sold') THEN 'sold'
                    ELSE 'available'
                END
        ");

        // Remove old status field
        Schema::table('cars', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            // Add back the old status field
            $table->string('status')->after('user_id');
        });

        // Migrate data back to single status field
        DB::statement("
            UPDATE cars SET 
                status = CASE 
                    WHEN moderation_status = 'published' AND car_status = 'available' THEN 'published'
                    WHEN moderation_status = 'published' AND car_status = 'reserved' THEN 'reserved'
                    WHEN moderation_status = 'published' AND car_status = 'sold' THEN 'sold'
                    WHEN moderation_status IN ('pending', 'rejected') THEN 'draft'
                    ELSE 'draft'
                END
        ");

        Schema::table('cars', function (Blueprint $table) {
            $table->dropColumn(['moderation_status', 'car_status']);
        });
    }
};
