<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable("user_insurance_deposits")) {
            Schema::create("user_insurance_deposits", function (Blueprint $table) {
                $table->id();
                $table->foreignId("user_id")->constrained("users")->onDelete("cascade");
                $table->decimal("amount", 10, 2);
                $table->enum("status", ["pending", "paid", "refunded", "forfeited"])->default("pending");
                $table->foreignId("payment_id")->nullable()->constrained("payments");
                $table->foreignId("refund_payment_id")->nullable()->constrained("payments");
                $table->timestamp("paid_at")->nullable();
                $table->timestamp("refunded_at")->nullable();
                $table->timestamps();

                // Indexes
                $table->index("user_id");
                $table->index("status");

            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists("user_insurance_deposits");
    }
};
