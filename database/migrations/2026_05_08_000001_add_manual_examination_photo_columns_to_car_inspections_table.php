<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $photoColumns = [
        'photo_front',
        'photo_back',
        'photo_left',
        'photo_right',
        'photo_interior_front',
        'photo_interior_back',
        'photo_engine',
        'photo_trunk',
        'photo_odometer',
        'photo_dashboard',
        'photo_vin_plate',
        'photo_tires',
        'photo_undercarriage',
    ];

    public function up(): void
    {
        Schema::table('car_inspections', function (Blueprint $table) {
            foreach ($this->photoColumns as $column) {
                if (!Schema::hasColumn('car_inspections', $column)) {
                    $table->string($column)->nullable()->after('metadata');
                }
            }
        });

        foreach ($this->photoColumns as $column) {
            if (!Schema::hasColumn('car_inspections', $column)) {
                continue;
            }

            DB::table('car_inspections')
                ->whereNotNull($column)
                ->orderBy('id')
                ->select(['id', $column])
                ->chunkById(200, function ($rows) use ($column) {
                    foreach ($rows as $row) {
                        $normalized = preg_replace(
                            '#^(public/|storage/)+#',
                            '',
                            ltrim(str_replace('\\', '/', (string) $row->{$column}), '/')
                        );

                        if ($normalized !== $row->{$column}) {
                            DB::table('car_inspections')
                                ->where('id', $row->id)
                                ->update([$column => $normalized]);
                        }
                    }
                });
        }
    }

    public function down(): void
    {
        // Intentionally keep uploaded manual examination image references on rollback.
    }
};
