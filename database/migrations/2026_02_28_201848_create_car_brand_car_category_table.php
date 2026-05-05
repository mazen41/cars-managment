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
        Schema::create('car_brand_car_category', function (Blueprint $table) {
            $table->unsignedBigInteger('car_brand_id');
            $table->unsignedBigInteger('car_category_id');

            // Define foreign keys
            $table->foreign('car_brand_id')->references('id')->on('car_brands')->onDelete('cascade');
            $table->foreign('car_category_id')->references('id')->on('car_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_brand_car_category');
    }
};
