<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('car_inspections', function (Blueprint $table) {


            $table->dropForeign(['inspector_id']);
            $table->unsignedBigInteger('inspector_id')->nullable()->change();
            $table->foreign('inspector_id')
                ->references('id')
                ->on('car_inspectors')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('car_inspections', function (Blueprint $table) {


            $table->dropForeign(['inspector_id']);

            $table->unsignedBigInteger('inspector_id')->nullable()->change();
            $table->foreign('inspector_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }
};
