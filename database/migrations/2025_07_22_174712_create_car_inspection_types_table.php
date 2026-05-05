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
        Schema::create("car_inspection_types", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("slug")->unique();
            $table->text("description")->nullable();
            $table->boolean("is_active")->default(true);
            $table->integer("sort_order")->default(0);
            $table->json("metadata")->nullable(); // For additional configuration
            $table->timestamps();

            $table->index(["is_active", "sort_order"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("car_inspection_types");
    }
};
