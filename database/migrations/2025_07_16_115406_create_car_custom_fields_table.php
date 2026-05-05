<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('car_custom_fields', function (Blueprint $table) {

            $table->id();
            $table->string('name');
            $table->string('type', 60);
            $table->integer('order')->default(999);
            $table->timestamps();
            $table->boolean('required')->default(false);
            $table->string('icon')->nullable();
        });

         Schema::create('car_custom_field_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_custom_field_id')->constrained('car_custom_fields')->onDelete('cascade');
            $table->string('lang');
            $table->text('name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_custom_fields');
        Schema::dropIfExists('car_custom_field_translations');
    }
};
