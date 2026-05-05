<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CarInspectionTypePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Create permissions for car inspection types management
        $permissions = [
            "view_car_inspection_types",
            "create_car_inspection_types",
            "edit_car_inspection_types",
            "delete_car_inspection_types",
            "duplicate_car_inspection_types",
            "manage_car_inspection_type_sections",
            "manage_car_inspection_type_fields",
            "toggle_car_inspection_type_status",
            "bulk_update_car_inspection_types",
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                "name" => $permission,
                "section" => "car_inspection_types",
                "guard_name" => "web",
            ]);
        }

        // Give admin role all car inspection type management permissions
        $adminRole = Role::where("name", "admin")->first();
        if ($adminRole) {
            foreach ($permissions as $permission) {
                $perm = Permission::where("name", $permission)->first();
                if ($perm) {
                    $adminRole->givePermissionTo($perm);
                }
            }
        }

        // Give staff role limited permissions
        $staffRole = Role::where("name", "staff")->first();
        if ($staffRole) {
            $staffPermissions = [
                "view_car_inspection_types",
                "edit_car_inspection_types",
                "manage_car_inspection_type_sections",
                "manage_car_inspection_type_fields",
            ];

            foreach ($staffPermissions as $permission) {
                $perm = Permission::where("name", $permission)->first();
                if ($perm) {
                    $staffRole->givePermissionTo($perm);
                }
            }
        }

        // Give manager role most permissions except delete
        $managerRole = Role::where("name", "manager")->first();
        if ($managerRole) {
            $managerPermissions = [
                "view_car_inspection_types",
                "create_car_inspection_types",
                "edit_car_inspection_types",
                "duplicate_car_inspection_types",
                "manage_car_inspection_type_sections",
                "manage_car_inspection_type_fields",
                "toggle_car_inspection_type_status",
            ];

            foreach ($managerPermissions as $permission) {
                $perm = Permission::where("name", $permission)->first();
                if ($perm) {
                    $managerRole->givePermissionTo($perm);
                }
            }
        }

        $this->command->info(
            "Car Inspection Type permissions seeded successfully!",
        );
    }
}
