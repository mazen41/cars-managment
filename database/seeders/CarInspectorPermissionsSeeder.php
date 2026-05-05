<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CarInspectorPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeder.
     *
     * @return void
     */
    public function run()
    {
        // Create permissions for car inspectors management
        $permissions = [
            'view_car_inspectors',
            'create_car_inspectors',
            'edit_car_inspectors',
            'delete_car_inspectors',
            'manage_car_inspector_payments',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create car_inspector role if it doesn't exist
        $carInspectorRole = Role::firstOrCreate(['name' => 'car_inspector']);

        // Give car inspectors basic permissions for their own data
        $carInspectorPermissions = [
            'view_car_inspections',
            'edit_car_inspections',
            'start_car_inspections',
            'complete_car_inspections',
        ];

        foreach ($carInspectorPermissions as $permission) {
            $perm = Permission::firstOrCreate(['name' => $permission]);
            $carInspectorRole->givePermissionTo($perm);
        }

        // Give admin role all car inspector management permissions
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            foreach ($permissions as $permission) {
                $perm = Permission::where('name', $permission)->first();
                if ($perm) {
                    $adminRole->givePermissionTo($perm);
                }
            }
        }

        // Give staff role view permissions
        $staffRole = Role::where('name', 'staff')->first();
        if ($staffRole) {
            $staffPermissions = ['view_car_inspectors'];
            foreach ($staffPermissions as $permission) {
                $perm = Permission::where('name', $permission)->first();
                if ($perm) {
                    $staffRole->givePermissionTo($perm);
                }
            }
        }

        $this->command->info('Car Inspector permissions seeded successfully!');
    }
}
