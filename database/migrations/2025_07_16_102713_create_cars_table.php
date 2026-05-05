<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable("cars")) {
            Schema::create("cars", function (Blueprint $table) {
                $table->id();
                $table->text("description");
                $table->foreignId("model_id")->constrained("car_models");
                $table->foreignId("brand_id")->constrained("car_brands");
                $table->string("color");
                $table->enum("condition", ["new", "used"]);
                $table->decimal("milage", 10, 2)->default(0);
                $table->year("manufacture_year");
                $table->longText("photos")->nullable();
                $table->integer("main_photo")->nullable();
                $table->string("transmission")->nullable();
                $table->string("fuel_type")->nullable();
                $table->string("location")->nullable();
                $table->decimal("price")->nullable();
                $table->foreignId("country_id")->default(1)->nullable();
                $table->foreignId("state_id")->nullable();
                $table->foreignId("city_id")->nullable();
                $table
                    ->foreignId("user_id")
                    ->constrained("users")
                    ->onDelete("cascade");
                $table->string("status");
                $table->timestamps();
            });
        }

        if (!Schema::hasTable("cars_translations")) {
            Schema::create("cars_translations", function (Blueprint $table) {
                $table->string("lang_code");
                $table->foreignId("car_id");
                $table->string("name", 255)->nullable();
                $table->text("description")->nullable();

                $table->primary(
                    ["lang_code", "car_id"],
                    "cars_translations_primary"
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists("cars");
        Schema::dropIfExists("cars_translations");
    }
};
