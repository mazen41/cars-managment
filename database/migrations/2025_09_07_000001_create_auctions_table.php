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
        Schema::create('auctions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('car_id')->constrained('cars')->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('start_time');
            $table->timestamp('end_time');
            $table->decimal('reserve_price', 15, 2);
            $table->decimal('starting_bid', 15, 2);
            $table->decimal('current_bid', 15, 2)->default(0);
            $table->foreignId('current_bidder_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('bid_increment', 15, 2)->default(100.00);
            $table->enum('status', ['pending', 'active', 'ended', 'cancelled'])->default('pending');
            $table->boolean('auto_extend_enabled')->default(true);
            $table->integer('extend_duration')->default(120); // seconds
            $table->integer('max_extensions')->default(5);
            $table->integer('extensions_count')->default(0);
            $table->foreignId('winner_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('final_price', 15, 2)->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index('status');
            $table->index('start_time');
            $table->index('end_time');
            $table->index('car_id');
            $table->index('seller_id');
            $table->index(['status', 'start_time']);
            $table->index(['status', 'end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auctions');
    }
};