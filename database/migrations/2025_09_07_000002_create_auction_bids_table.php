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
        Schema::create('auction_bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auction_id')->constrained('auctions')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->timestamp('bid_time')->useCurrent();
            $table->boolean('is_winning')->default(false);
            $table->boolean('is_valid')->default(true);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index('auction_id');
            $table->index('user_id');
            $table->index('bid_time');
            $table->index('amount');
            $table->index(['auction_id', 'bid_time']);
            $table->index(['auction_id', 'amount']);
            $table->index(['user_id', 'bid_time']);
            
            // Unique constraint to prevent duplicate bids
            $table->unique(['auction_id', 'user_id', 'amount'], 'unique_auction_user_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auction_bids');
    }
};