<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CarPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
      public function run(): void
    {
        $permissions = [
            'view_cars',
            'edit_cars',
            'delete_cars'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission], ['section' => 'cars'] );
        }

        // Assign all permissions to Super Admin role
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($permissions);
        }

        // Assign all permissions to Tech Support role
        $techSupportRole = Role::where('name', 'Tech Support')->first();
        if ($techSupportRole) {
            $techSupportRole->givePermissionTo($permissions);
        }

        $this->command->info('Car management permissions created and assigned successfully.');
    }
}
