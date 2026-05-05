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
        // Fix table brand id type to unsigned big integer
        if(Schema::hasTable('brands')){
            Schema::table('brands', function (Blueprint $table) {
               $table->bigIncrements('id')->change();
            });
        }

         // Fix requested_products table category_id type to unsigned big integer
        if(Schema::hasTable('requested_products')){
            Schema::table('requested_products', function (Blueprint $table) {
                $table->dropForeign('requested_products_category_id_foreign');
            });

            Schema::table('requested_products', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->change();
            });
        }

         // Fix categories table id type to unsigned big integer
        if(Schema::hasTable('categories')){
            Schema::table('categories', function (Blueprint $table) {
                $table->bigIncrements('id')->change();
            });
        }

        // Add foreign key constraint to requested_products table
        Schema::table('requested_products', function (Blueprint $table) {
            $table->foreign('category_id')
                  ->references('id')
                  ->on('categories')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
   public function down(): void
    {
        if (Schema::hasTable('requested_products')) {
            // Drop the "new" foreign key first
            Schema::table('requested_products', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
            });

            // Revert child column (Standard Integer, likely not null originally)
            Schema::table('requested_products', function (Blueprint $table) {
                $table->integer('category_id')->nullable(false)->change();
            });
        }

        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->increments('id')->change(); // increments() is signed Integer
            });
        }

        if (Schema::hasTable('brands')) {
            Schema::table('brands', function (Blueprint $table) {
                $table->increments('id')->change();
            });
        }

        // Re-apply the original foreign key constraint
        if (Schema::hasTable('requested_products') && Schema::hasTable('categories')) {
            Schema::table('requested_products', function (Blueprint $table) {
                $table->foreign('category_id')
                      ->references('id')
                      ->on('categories')
                      ->onDelete('set null');
            });
        }
    }
};
