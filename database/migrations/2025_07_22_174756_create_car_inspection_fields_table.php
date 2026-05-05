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
        Schema::create("car_inspection_fields", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("section_id")
                ->constrained("car_inspection_sections")
                ->onDelete("cascade");
            $table->string("name");
            $table->string("slug");
            $table->text("description")->nullable();
            $table->enum("field_type", [
                "text",
                "textarea",
                "boolean",
                "number",
                "select",
                "checkbox",
                "radio",
                "date",
                "email",
                "url",
            ]);
            $table->json("field_options")->nullable(); // For select/radio options, validation rules, etc.
            $table->boolean("is_required")->default(false);
            $table->boolean("is_active")->default(true);
            $table->integer("sort_order")->default(0);
            $table->string("placeholder")->nullable();
            $table->string("help_text")->nullable();
            $table->json("validation_rules")->nullable(); // Store validation rules as JSON
            $table->json("metadata")->nullable(); // For additional configuration
            $table->timestamps();

            $table->index(
                ["section_id", "is_active", "sort_order"],
                "fields_section_status_order_idx",
            );
            $table->unique(
                ["section_id", "slug"],
                "fields_section_slug_unique",
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("car_inspection_fields");
    }
};
