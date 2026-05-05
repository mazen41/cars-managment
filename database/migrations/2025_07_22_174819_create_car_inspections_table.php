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
        Schema::create("car_inspections", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("car_id")
                ->constrained("cars")
                ->onDelete("cascade");
            $table
                ->foreignId("inspection_type_id")
                ->constrained("car_inspection_types")
                ->onDelete("cascade");
            $table
                ->foreignId("inspector_id")
                ->nullable()
                ->constrained("users")
                ->onDelete("set null");
            $table
                ->foreignId("requested_by")
                ->constrained("users")
                ->onDelete("cascade");
            $table->string("inspection_number")->unique();
            $table
                ->enum("status", [
                    "pending",
                    "in_progress",
                    "completed",
                    "cancelled",
                    "failed",
                ])
                ->default("pending");
            $table->timestamp("scheduled_at")->nullable();
            $table->timestamp("started_at")->nullable();
            $table->timestamp("completed_at")->nullable();
            $table->decimal("total_score", 5, 2)->nullable();
            $table
                ->enum("overall_condition", [
                    "excellent",
                    "good",
                    "fair",
                    "poor",
                    "critical",
                ])
                ->nullable();
            $table->text("inspector_notes")->nullable();
            $table->text("recommendations")->nullable();
            $table->json("summary")->nullable(); // Store inspection summary data
            $table->json("metadata")->nullable(); // For additional data
            $table->timestamps();

            $table->index(["car_id", "status"]);
            $table->index(["inspection_type_id", "status"]);
            $table->index(["inspector_id", "status"]);
            $table->index(["requested_by", "status"]);
            $table->index("scheduled_at");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("car_inspections");
    }
};
