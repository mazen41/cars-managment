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
        Schema::table('car_reservations', function (Blueprint $table) {
            $table->dropColumn('reservation_amount');
            $table->dropColumn('payment_method');
            $table->dropColumn('payment_status');
            $table->dropColumn('payment_details');
            $table->dropColumn('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_reservations', function (Blueprint $table) {
           $table->decimal('reservation_amount', 10, 2)->nullable();
            $table->string('payment_method')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->json('payment_details')->nullable();
            $table->string('transaction_id')->nullable();
        });
    }
};
