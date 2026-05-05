<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable("bids")) {
            Schema::create("bids", function (Blueprint $table) {
                $table->id();
                $table->foreignId("auction_item_id")->constrained("auction_items")->onDelete("cascade");
                $table->foreignId("bidder_id")->constrained("users");
                $table->decimal("amount", 10, 2);
                $table->enum("status", ["pending", "accepted", "rejected", "outbid"])->default("pending");
                $table->string("rejection_reason")->nullable();
                $table->string("bid_token")->unique();
                $table->string("ip_address")->nullable();
                $table->text("user_agent")->nullable();
                $table->timestamps();

                // Indexes
                $table->index("auction_item_id");
                $table->index("bidder_id");
                $table->index("status");
                $table->index("created_at");
                $table->index("bid_token");
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists("bids");
    }
};
