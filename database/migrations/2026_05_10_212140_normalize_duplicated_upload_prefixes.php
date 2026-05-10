<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('uploads')) {
            DB::table('uploads')
                ->where('file_name', 'like', 'uploads/uploads/%')
                ->update([
                    'file_name' => DB::raw("REGEXP_REPLACE(file_name, '^uploads/uploads/', 'uploads/')")
                ]);
        }

        if (Schema::hasTable('car_inspectors')) {
            DB::table('car_inspectors')
                ->where('image', 'like', 'uploads/uploads/%')
                ->update([
                    'image' => DB::raw("REGEXP_REPLACE(image, '^uploads/uploads/', 'uploads/')")
                ]);

            DB::table('car_inspectors')
                ->where('banner_image', 'like', 'uploads/uploads/%')
                ->update([
                    'banner_image' => DB::raw("REGEXP_REPLACE(banner_image, '^uploads/uploads/', 'uploads/')")
                ]);
        }
    }

    public function down(): void
    {
        // no-op
    }
};

