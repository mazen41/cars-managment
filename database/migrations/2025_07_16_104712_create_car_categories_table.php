<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable("car_categories")) {
            Schema::create("car_categories", function (Blueprint $table) {
                $table->id();
                $table->string("name", 120);
                $table->string("description", 400)->nullable();
                $table->string("status", 60)->default("published");
                $table->integer("order")->default(0)->unsigned();
                $table->foreignId("parent_id")->default(0);
                $table->tinyInteger("is_default")->default(0);
                $table->string("image", 60)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable("car_category_translations")) {
            Schema::create("car_category_translations", function (
                Blueprint $table
            ) {
                $table->string("lang");
                $table->foreignId("car_category_id");
                $table->string("name", 255)->nullable();

                $table->primary(
                    ["lang", "car_category_id"],
                    "car_category_translations_primary"
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists("car_categories");
        Schema::dropIfExists("car_category_translations");
    }
};
