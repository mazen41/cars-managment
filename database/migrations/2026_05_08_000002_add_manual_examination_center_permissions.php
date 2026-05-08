<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('guard_name')->default('web');
                $table->foreignId('center_id')->nullable()->constrained('car_inspectors')->cascadeOnDelete();
                $table->boolean('can_manual_examination')->default(true);
                $table->timestamps();

                $table->unique(['name', 'guard_name']);
                $table->unique('center_id');
            });

            return;
        }

        Schema::table('permissions', function (Blueprint $table) {
            if (!Schema::hasColumn('permissions', 'center_id')) {
                $table->foreignId('center_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('car_inspectors')
                    ->cascadeOnDelete();
            }

            if (!Schema::hasColumn('permissions', 'can_manual_examination')) {
                $table->boolean('can_manual_examination')->default(true)->after('center_id');
            }
        });
    }

    public function down(): void
    {
        // Keep permission data intact on rollback to avoid removing Spatie permission rows.
    }
};
