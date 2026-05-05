<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable("car_brands")) {
            Schema::create("car_brands", function (Blueprint $table) {
                $table->id();
                $table->string("name", 255);
                $table->string("logo");
                $table->string("status", 60)->default("pending");
                $table->timestamps();
            });
        }

        if (!Schema::hasTable("car_brand_translations")) {
            Schema::create("car_brand_translations", function (
                Blueprint $table
            ) {
                $table->string("lang");
                $table->foreignId("car_brand_id");
                $table->string("name", 255)->nullable();

                $table->primary(
                    ["lang", "car_brand_id"],
                    "car_brand_translations_primary"
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists("car_brands");
        Schema::dropIfExists("car_brand_translations");
    }
};
