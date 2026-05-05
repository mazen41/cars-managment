<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CarInspectionPaymentPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Create permissions for car inspection payments management
        $permissions = [
            "view_car_inspection_payments",
            "create_car_inspection_payments",
            "edit_car_inspection_payments",
            "delete_car_inspection_payments",
            "process_car_inspection_payments",
            "complete_car_inspection_payments",
            "cancel_car_inspection_payments",
            "bulk_update_car_inspection_payments",
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                "name" => $permission,
                "section" => "car_inspection_payments",
                "guard_name" => "web",
            ]);
        }

        // Give admin role all car inspection payment management permissions
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
                "view_car_inspection_payments",
                "edit_car_inspection_payments",
                "process_car_inspection_payments",
                "complete_car_inspection_payments",
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
                "view_car_inspection_payments",
                "create_car_inspection_payments",
                "edit_car_inspection_payments",
                "process_car_inspection_payments",
                "complete_car_inspection_payments",
                "cancel_car_inspection_payments",
                "bulk_update_car_inspection_payments",
            ];

            foreach ($managerPermissions as $permission) {
                $perm = Permission::where("name", $permission)->first();
                if ($perm) {
                    $managerRole->givePermissionTo($perm);
                }
            }
        }

        // Give inspector role basic permissions for payments
        $inspectorRole = Role::where("name", "inspector")->first();
        if ($inspectorRole) {
            $inspectorPermissions = [
                "view_car_inspection_payments",
            ];

            foreach ($inspectorPermissions as $permission) {
                $perm = Permission::where("name", $permission)->first();
                if ($perm) {
                    $inspectorRole->givePermissionTo($perm);
                }
            }
        }

        $this->command->info(
            "Car Inspection Payment permissions seeded successfully!",
        );
    }
}
