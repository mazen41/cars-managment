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
        Schema::table('user_insurance_deposits', function (Blueprint $table) {
            $table->boolean('refund_requested')->default(false)->after('paid_at');
            $table->enum("status", ["pending", "paid", "refunded", "cancelled"])->default("pending")->change();
            $table->timestamp('refund_requested_at')->nullable()->after('refund_requested');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_insurance_deposits', function (Blueprint $table) {
            $table->dropColumn('refund_requested');
            $table->dropColumn('refund_requested_at');
        });
    }
};
