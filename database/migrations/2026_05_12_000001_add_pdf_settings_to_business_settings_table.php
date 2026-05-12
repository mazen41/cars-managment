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
        // Insert default PDF settings into business_settings table
        \DB::table('business_settings')->insert([
            [
                'type' => 'pdf_header_image',
                'value' => '',
                'lang' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'pdf_footer_image',
                'value' => '',
                'lang' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'pdf_disclaimer',
                'value' => '',
                'lang' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \DB::table('business_settings')
            ->whereIn('type', ['pdf_header_image', 'pdf_footer_image', 'pdf_disclaimer'])
            ->delete();
    }
};
