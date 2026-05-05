<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable("auction_listing_requests")) {
            Schema::create("auction_listing_requests", function (Blueprint $table) {
                $table->id();
                $table->foreignId("car_id")->constrained("cars")->onDelete("cascade");
                $table->foreignId("seller_id")->constrained("users");
                $table->decimal("requested_starting_price", 10, 2);
                $table->decimal("requested_reserve_price", 10, 2)->nullable();
                $table->enum("status", ["pending", "approved", "rejected"])->default("pending");
                $table->text("admin_notes")->nullable();
                $table->foreignId("reviewed_by")->nullable()->constrained("users");
                $table->timestamp("reviewed_at")->nullable();
                $table->timestamps();

                // Indexes
                $table->index("car_id");
                $table->index("seller_id");
                $table->index("status");
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists("auction_listing_requests");
    }
};
