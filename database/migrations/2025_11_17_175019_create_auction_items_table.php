<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable("auction_items")) {
            Schema::create("auction_items", function (Blueprint $table) {
                $table->id();
                $table->foreignId("auction_room_id")->constrained("auction_rooms")->onDelete("cascade");
                $table->foreignId("car_id")->constrained("cars")->onDelete("cascade");
                $table->foreignId("seller_id")->constrained("users");
                $table->integer("sequence_order");
                $table->decimal("starting_price", 10, 2);
                $table->decimal("reserve_price", 10, 2)->nullable();
                $table->decimal("current_price", 10, 2)->nullable();
                $table->foreignId("current_winner_id")->nullable()->constrained("users");
                $table->enum("status", ["pending", "active", "sold", "unsold", "withdrawn", "offer_accepted"])->default("pending");
                $table->timestamp("started_at")->nullable();
                $table->timestamp("ends_at")->nullable();
                $table->timestamp("finalized_at")->nullable();
                $table->integer("total_bids")->default(0);
                $table->integer("total_extensions")->default(0);
                $table->timestamps();

                // Indexes
                $table->index("auction_room_id");
                $table->index("car_id");
                $table->index("seller_id");
                $table->index("status");
                $table->index("sequence_order");

            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists("auction_items");
    }
};
