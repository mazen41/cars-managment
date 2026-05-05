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
        Schema::create('customer_product_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_product_id')->constrained('customer_products')->onDelete('cascade');
            $table->string('lang', 10);
            $table->string('name');
            $table->text('description');
            $table->timestamps();

            // Unique constraint to prevent duplicate translations for same product and language
            $table->unique(['customer_product_id', 'lang']);
            
            // Index for performance
            $table->index(['customer_product_id', 'lang']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_product_translations');
    }
};
