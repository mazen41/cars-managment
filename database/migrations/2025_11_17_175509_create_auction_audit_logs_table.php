<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable("auction_audit_logs")) {
            Schema::create("auction_audit_logs", function (Blueprint $table) {
                $table->id();
                $table->foreignId("auction_room_id")->nullable()->constrained("auction_rooms")->onDelete("cascade");
                $table->foreignId("auction_item_id")->nullable()->constrained("auction_items")->onDelete("cascade");
                $table->foreignId("user_id")->nullable()->constrained("users");
                $table->string("action");
                $table->json("details")->nullable();
                $table->string("ip_address")->nullable();
                $table->timestamp("created_at");

                // Indexes
                $table->index("auction_room_id");
                $table->index("auction_item_id");
                $table->index("user_id");
                $table->index("action");
                $table->index("created_at");
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists("auction_audit_logs");
    }
};
