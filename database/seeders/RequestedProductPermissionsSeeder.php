<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RequestedProductPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Create permissions for requested products management
        $permissions = [
            "view_requested_products",
            "create_requested_products",
            "edit_requested_products",
            "delete_requested_products",
            "approve_requested_products",
            "reject_requested_products",
            "publish_requested_products",
            "bulk_update_requested_products",
            "export_requested_products",
            "manage_requested_product_status",
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                "name" => $permission,
                "section" => "requested_products",
                "guard_name" => "web",
            ]);
        }

        // Give admin role all requested product management permissions
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
                "view_requested_products",
                "edit_requested_products",
                "approve_requested_products",
                "reject_requested_products",
                "manage_requested_product_status",
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
                "view_requested_products",
                "create_requested_products",
                "edit_requested_products",
                "approve_requested_products",
                "reject_requested_products",
                "publish_requested_products",
                "bulk_update_requested_products",
                "export_requested_products",
                "manage_requested_product_status",
            ];

            foreach ($managerPermissions as $permission) {
                $perm = Permission::where("name", $permission)->first();
                if ($perm) {
                    $managerRole->givePermissionTo($perm);
                }
            }
        }

        $this->command->info(
            "Requested Product permissions seeded successfully!",
        );
    }
}