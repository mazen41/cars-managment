<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('car_inspector_payment_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_inspector_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['earning', 'payment', 'adjustment'])->default('earning');
            $table->decimal('amount', 10, 2);
            $table->text('description')->nullable();
            $table->string('payment_method')->nullable();
            $table->json('payment_details')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('transaction_reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('car_inspector_id');
            $table->index('type');
            $table->index('status');
            $table->index('processed_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('car_inspector_payment_histories');
    }
};
