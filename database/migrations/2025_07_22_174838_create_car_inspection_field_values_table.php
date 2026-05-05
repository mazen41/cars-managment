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
        Schema::create("car_inspection_field_values", function (
            Blueprint $table,
        ) {
            $table->id();
            $table
                ->foreignId("inspection_id")
                ->constrained("car_inspections")
                ->onDelete("cascade");
            $table
                ->foreignId("field_id")
                ->constrained("car_inspection_fields")
                ->onDelete("cascade");
            $table->text("value")->nullable();
            $table->json("file_attachments")->nullable(); // For file uploads
            $table->decimal("score", 5, 2)->nullable(); // Individual field score
            $table->text("notes")->nullable(); // Field-specific notes
            $table->boolean("is_flagged")->default(false); // For issues/concerns
            $table->string("flag_reason")->nullable();
            $table->json("metadata")->nullable(); // For additional data
            $table->timestamps();

            $table->unique(
                ["inspection_id", "field_id"],
                "field_values_inspection_field_unique",
            );
            $table->index(
                ["inspection_id", "is_flagged"],
                "field_values_inspection_flagged_idx",
            );
            $table->index("field_id", "field_values_field_id_idx");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("car_inspection_field_values");
    }
};
