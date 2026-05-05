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
        Schema::create("car_inspection_sections", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("inspection_type_id")
                ->constrained("car_inspection_types")
                ->onDelete("cascade");
            $table->string("name");
            $table->string("slug");
            $table->text("description")->nullable();
            $table->boolean("is_active")->default(true);
            $table->integer("sort_order")->default(0);
            $table->json("metadata")->nullable(); // For additional configuration
            $table->timestamps();

            $table->index(
                ["inspection_type_id", "is_active", "sort_order"],
                "sections_type_status_order_idx",
            );
            $table->unique(
                ["inspection_type_id", "slug"],
                "sections_type_slug_unique",
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("car_inspection_sections");
    }
};
