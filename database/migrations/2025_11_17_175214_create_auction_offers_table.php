<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable("auction_offers")) {
            Schema::create("auction_offers", function (Blueprint $table) {
                $table->id();
                $table->foreignId("auction_item_id")->constrained("auction_items")->onDelete("cascade");
                $table->foreignId("buyer_id")->constrained("users");
                $table->foreignId("seller_id")->constrained("users");
                $table->decimal("amount", 10, 2);
                $table->enum("status", ["pending", "accepted", "rejected", "expired", "withdrawn"])->default("pending");
                $table->text("message")->nullable();
                $table->text("seller_response")->nullable();
                $table->timestamp("responded_at")->nullable();
                $table->timestamp("expires_at")->nullable();
                $table->timestamps();

                // Indexes
                $table->index("auction_item_id");
                $table->index("buyer_id");
                $table->index("seller_id");
                $table->index("status");
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists("auction_offers");
    }
};
