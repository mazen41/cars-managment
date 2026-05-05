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
        // FAQ table creation
        if (!Schema::hasTable('faqs')) {
            Schema::create('faqs', function (Blueprint $table) {
                $table->id();
                $table->string('question', 500);
                $table->text('answer');
                $table->string('type')->default('general');
                $table->string('slug')->unique();
                $table->boolean('is_published')->default(false);
                $table->integer('sort_order')->default(0);
                $table->integer('view_count')->default(0);
                $table->timestamps();

                // Indexes for performance
                $table->index('is_published', 'idx_published');
                $table->index(['type', 'sort_order'], 'idx_type_sort');
                $table->index('view_count', 'idx_view_count');
            });
        }

        // FAQ Translations table creation
        if (!Schema::hasTable('faq_translations')) {
            Schema::create('faq_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faq_id')->constrained('faqs')->onDelete('cascade');
            $table->string('locale', 10);
            $table->string('question', 500);
            $table->text('answer');

            // Unique constraint to prevent duplicate translations for same FAQ/locale
            $table->unique(['faq_id', 'locale'], 'unique_faq_locale');

            // Index for locale lookups
            $table->index('locale', 'idx_locale');
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faq_translations');
        Schema::dropIfExists('faqs');
    }
};
