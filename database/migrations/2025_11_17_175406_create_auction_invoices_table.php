<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable("auction_invoices")) {
            Schema::create("auction_invoices", function (Blueprint $table) {
                $table->id();
                $table->foreignId("auction_item_id")->constrained("auction_items")->onDelete("cascade");
                $table->enum("invoice_type", ["buyer_payment", "seller_payout"]);
                $table->foreignId("user_id")->constrained("users");
                $table->decimal("amount", 10, 2);
                $table->decimal("commission_amount", 10, 2)->nullable();
                $table->decimal("net_amount", 10, 2);
                $table->foreignId("payment_id")->nullable()->constrained("payments");
                $table->enum("status", ["pending", "paid", "cancelled"])->default("pending");
                $table->timestamp("due_date")->nullable();
                $table->timestamp("paid_at")->nullable();
                $table->timestamps();

                // Indexes
                $table->index("auction_item_id");
                $table->index("user_id");
                $table->index("invoice_type");
                $table->index("status");
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists("auction_invoices");
    }
};
