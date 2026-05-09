<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manual_examination_center_inspection_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('center_id')
                ->constrained('car_inspectors')
                ->cascadeOnDelete();
            $table->foreignId('inspection_type_id')
                ->constrained('car_inspection_types')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['center_id', 'inspection_type_id'], 'manual_exam_center_type_unique');
            $table->index(['inspection_type_id', 'center_id'], 'manual_exam_type_center_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_examination_center_inspection_types');
    }
};

