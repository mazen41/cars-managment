<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarCustomFieldOptionsTable extends Migration
{
    public function up()
    {
        Schema::create("car_custom_field_options", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("custom_field_id")
                ->constrained("car_custom_fields")
                ->onDelete("cascade");
            $table->string("label");
            $table->string("value");
            $table->integer("order")->default(0);
            $table->timestamps();
        });

        Schema::create("car_custom_field_option_translations", function (Blueprint $table) {
            $table->string("lang");
            $table->unsignedBigInteger("car_custom_field_option_id");

            $table->foreign("car_custom_field_option_id", "cf_option_translation_fk")
                ->references("id")
                ->on("car_custom_field_options")
                ->onDelete("cascade");

            $table->string("label")->nullable();
            $table->timestamps();

            $table->primary(["lang", "car_custom_field_option_id"], "car_custom_field_option_translations_primary");
        });
    }

    public function down()
    {
        Schema::dropIfExists("car_custom_field_options");
        Schema::dropIfExists("car_custom_field_option_translations");
    }
}
