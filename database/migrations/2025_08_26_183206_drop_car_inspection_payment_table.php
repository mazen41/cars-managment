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
       Schema::dropIfExists('car_inspection_payments');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
          Schema::create("car_inspection_payments", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("car_inspection_id")
                ->constrained("car_inspections")
                ->onDelete("cascade");
            $table->string("method");
            $table->string("status")
                ->default("pending");
            $table->text("details")->nullable();
            $table->boolean("is_manual_payment")->default(false);
            $table->string("transaction_id")->nullable();
            $table->string("reference_id")->nullable();
            $table->decimal("amount", 10, 2)->nullable();
            $table->timestamp("paid_at")->nullable();
            $table->timestamps();

            $table->unique("car_inspection_id");
            $table->index(["status", "method"]);
            $table->index("transaction_id");
            $table->index("reference_id");
            $table->index("paid_at");
        });
    }
};
