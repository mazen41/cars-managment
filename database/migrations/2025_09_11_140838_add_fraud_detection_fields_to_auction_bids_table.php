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
        Schema::table('auction_bids', function (Blueprint $table) {
            // Add fraud detection fields
            $table->integer('fraud_score')->default(0)->after('user_agent');
            $table->json('fraud_flags')->nullable()->after('fraud_score');
            
            // Add index for fraud score queries
            $table->index('fraud_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auction_bids', function (Blueprint $table) {
            $table->dropIndex(['fraud_score']);
            $table->dropColumn(['fraud_score', 'fraud_flags']);
        });
    }
};