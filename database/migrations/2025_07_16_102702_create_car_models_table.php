<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable("car_models")) {
            Schema::create("car_models", function (Blueprint $table) {
                $table->id();
                $table->string("name");
                $table->foreignId("brand_id")->constrained("car_brands");
                $table->string("status");
                $table->timestamps();
            });
        }

        if (!Schema::hasTable("car_model_translations")) {
            Schema::create("car_model_translations", function (
                Blueprint $table
            ) {
                $table->string("lang");
                $table->foreignId("car_model_id");
                $table->string("name", 255)->nullable();
                $table->primary(
                    ["lang", "car_model_id"],
                    "car_model_translations_primary"
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists("car_models");
        Schema::dropIfExists("car_model_translations");
    }
};
