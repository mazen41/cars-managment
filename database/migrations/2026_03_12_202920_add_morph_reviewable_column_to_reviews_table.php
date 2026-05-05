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
        Schema::table('reviews', function (Blueprint $table) {
            // remove product_id column
            $table->dropColumn('product_id');
            // add morph columns
            $table->unsignedBigInteger('reviewable_id')->nullable()->after('id');
            $table->string('reviewable_type')->nullable()->after('reviewable_id');
            $table->index(['reviewable_id', 'reviewable_type']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // add product_id column back
            $table->unsignedBigInteger('product_id')->nullable()->after('id');
            // remove morph columns
            $table->dropIndex(['reviewable_id', 'reviewable_type']);
            $table->dropColumn('reviewable_id');
            $table->dropColumn('reviewable_type');
        });
    }
};
