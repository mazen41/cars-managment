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
        Schema::create('customer_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->text('description');
            $table->enum('condition', ['new', 'used']);
            $table->decimal('price', 10, 2);
            $table->integer('category_id');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->integer('main_photo')->nullable();
            $table->json('photos')->nullable();
            $table->string('address', 500);
            $table->bigInteger('state_id');
            $table->foreign('state_id')->references('id')->on('states')->onDelete('cascade');
            $table->bigInteger('city_id');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
            $table->decimal('longitude', 10, 8)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->enum('moderation_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('availability_status', ['available', 'unavailable'])->default('available');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index('user_id');
            $table->index('category_id');
            $table->index('moderation_status');
            $table->index(['state_id', 'city_id']);
            $table->index(['moderation_status', 'availability_status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_products');
    }
};
