<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable("auction_rooms")) {
            Schema::create("auction_rooms", function (Blueprint $table) {
                $table->id();
                $table->string("name");
                $table->text("description")->nullable();
                $table->decimal("commission_percentage", 5, 2);
                $table->enum("bid_increment_type", ["percentage", "flat"]);
                $table->decimal("bid_increment_value", 10, 2);
                $table->integer("base_timer_seconds")->default(60);
                $table->integer("extension_seconds")->default(30);
                $table->decimal("insurance_deposit_amount", 10, 2)->nullable();
                $table->foreignId("currency_id")->nullable()->constrained("currencies");
                $table->enum("status", ["draft", "scheduled", "active", "completed", "cancelled"])->default("draft");
                $table->timestamp("scheduled_start_at")->nullable();
                $table->timestamp("started_at")->nullable();
                $table->timestamp("completed_at")->nullable();
                $table->foreignId("created_by")->constrained("users");
                $table->timestamps();

                // Indexes
                $table->index("status");
                $table->index("scheduled_start_at");
                $table->index("created_by");
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists("auction_rooms");
    }
};
