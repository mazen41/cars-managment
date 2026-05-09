<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manual_examination_center_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('center_id')
                ->constrained('car_inspectors')
                ->cascadeOnDelete();
            $table->boolean('can_manual_examination')->default(true);
            $table->timestamps();

            $table->unique('center_id');
            $table->index(['center_id', 'can_manual_examination']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_examination_center_permissions');
    }
};

