<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CustomerProductPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions for customer products management
        $permissions = [
            'view_customer_products',
            'moderate_customer_products', 
            'view_customer_product_analytics',
            'manage_customer_product_settings',
            'bulk_moderate_customer_products',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to admin and tech support roles
        $adminRole = Role::where('name', 'Admin')->first();
        $techSupportRole = Role::where('name', 'Tech Support')->first();

        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
        }

        if ($techSupportRole) {
            $techSupportRole->givePermissionTo($permissions);
        }

        $this->command->info('Customer Product permissions created and assigned successfully.');
    }
}