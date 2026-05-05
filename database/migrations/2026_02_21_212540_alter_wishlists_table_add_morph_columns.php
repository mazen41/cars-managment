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
        Schema::table('wishlists', function (Blueprint $table) {
            $table->string('wishlistable_type')->nullable()->after('id');
            $table->unsignedBigInteger('wishlistable_id')->nullable()->after('wishlistable_type');
        });
        // migrate existing data to new columns
        $wishlists = \App\Models\Wishlist::all();
        foreach ($wishlists as $wishlist) {
            $wishlist->wishlistable_type = 'App\Models\Product';
            $wishlist->wishlistable_id = $wishlist->product_id;
            $wishlist->save();
        }

        // drop old product_id column
        Schema::table('wishlists', function (Blueprint $table) {
            $table->dropColumn('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wishlists', function (Blueprint $table) {
            $table->dropColumn('wishlistable_type');
            $table->dropColumn('wishlistable_id');
        });
        // add back product_id column
        Schema::table('wishlists', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable()->after('id');
        });
        // migrate data back to product_id column
        $wishlists = \App\Models\Wishlist::all();
        foreach ($wishlists as $wishlist) {
            if ($wishlist->wishlistable_type == 'App\Models\Product') {
                $wishlist->product_id = $wishlist->wishlistable_id;
                $wishlist->save();
            }
            }
    }
};
