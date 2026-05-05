<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('car_features')) {
            Schema::create('car_features', function (Blueprint $table) {
                $table->id();
                $table->string('name', 255);
                $table->string('image', 60)->nullable();
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('car_has_feature')) {
            Schema::create('car_has_feature', function (Blueprint $table): void {
                $table->foreignId('car_id');
                $table->foreignId('feature_id');
            });
        }
        if (! Schema::hasTable('car_feature_translations')) {
            Schema::create('car_feature_translations', function (Blueprint $table) {
                $table->string('lang');
                $table->foreignId('car_feature_id');
                $table->string('name', 255)->nullable();

                $table->primary(['lang', 'car_feature_id'], 'car_feature_translations_primary');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('car_features');
        Schema::dropIfExists('car_feature_translations');
        Schema::dropIfExists('car_has_feature');
    }
};
