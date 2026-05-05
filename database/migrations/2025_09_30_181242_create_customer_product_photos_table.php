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
        Schema::create('customer_product_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_product_id')->constrained('customer_products')->onDelete('cascade');
            $table->integer('upload_id');
            $table->timestamps();

            // Unique constraint to prevent duplicate photo assignments
            $table->unique(['customer_product_id', 'upload_id']);
            
            // Index for performance
            $table->index('customer_product_id');
            $table->index('upload_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_product_photos');
    }
};
