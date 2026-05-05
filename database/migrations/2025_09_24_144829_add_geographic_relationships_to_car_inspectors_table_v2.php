<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table("car_inspectors", function (Blueprint $table) {
            // Add columns without foreign key constraints due to mixed engines and data types
            $table
                ->integer("country_id")
                ->unsigned()
                ->nullable()
                ->after("longitude");
            $table
                ->unsignedBigInteger("state_id")
                ->nullable()
                ->after("country_id");
            $table
                ->unsignedBigInteger("city_id")
                ->nullable()
                ->after("state_id");

            // Add indexes for better performance
            $table->index(["country_id", "state_id", "city_id"]);
            $table->index("country_id");
            $table->index("state_id");
            $table->index("city_id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("car_inspectors", function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(["country_id", "state_id", "city_id"]);
            $table->dropIndex(["country_id"]);
            $table->dropIndex(["state_id"]);
            $table->dropIndex(["city_id"]);

            // Drop columns
            $table->dropColumn(["country_id", "state_id", "city_id"]);
        });
    }
};
