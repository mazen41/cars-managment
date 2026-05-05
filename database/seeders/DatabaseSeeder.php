<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\ExternalOrderPriceAdjustmentSeeder;
use Database\Seeders\CarInspectorPermissionsSeeder;
use Database\Seeders\CarInspectionTypePermissionsSeeder;
use Database\Seeders\CarInspectionPaymentPermissionsSeeder;
use Database\Seeders\RequestedProductPermissionsSeeder;
use Database\Seeders\AuctionPermissionsSeeder;
use Database\Seeders\AuctionNotificationTypesSeeder;
use Database\Seeders\AuctionConfigurationSeeder;
use Database\Seeders\CustomerProductPermissionsSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            CarInspectorPermissionsSeeder::class,
            CarInspectionTypePermissionsSeeder::class,
            CarInspectionPaymentPermissionsSeeder::class,
            RequestedProductPermissionsSeeder::class,
            AuctionPermissionsSeeder::class,
            AuctionNotificationTypesSeeder::class,
            AuctionConfigurationSeeder::class,
            CustomerProductPermissionsSeeder::class,
        ]);

        $this->command->info(
            "Car inspection payment system has been set up successfully!",
        );
    }
}
