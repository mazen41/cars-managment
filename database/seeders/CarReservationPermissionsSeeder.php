<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CarReservationPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define car reservation permissions
        $permissions = [
            [
                'name' => 'view_car_reservations',
                'section' => 'Cars',
                'guard_name' => 'web'
            ],
            [
                'name' => 'create_car_reservations',
                'section' => 'Cars',
                'guard_name' => 'web'
            ],
            [
                'name' => 'edit_car_reservations',
                'section' => 'Cars',
                'guard_name' => 'web'
            ],
            [
                'name' => 'delete_car_reservations',
                'section' => 'Cars',
                'guard_name' => 'web'
            ],
            [
                'name' => 'confirm_car_reservations',
                'section' => 'Cars',
                'guard_name' => 'web'
            ],
            [
                'name' => 'cancel_car_reservations',
                'section' => 'Cars',
                'guard_name' => 'web'
            ],
            [
                'name' => 'update_car_reservation_payment',
                'section' => 'Cars',
                'guard_name' => 'web'
            ],
            [
                'name' => 'mark_car_as_sold',
                'section' => 'Cars',
                'guard_name' => 'web'
            ],
            [
                'name' => 'view_car_reservation_statistics',
                'section' => 'Cars',
                'guard_name' => 'web'
            ]
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name'], 'guard_name' => $permission['guard_name']],
                $permission
            );
        }

        // Get admin role
        $adminRole = Role::where('name', 'admin')->first();

        if ($adminRole) {
            // Give all car reservation permissions to admin
            $adminRole->givePermissionTo([
                'view_car_reservations',
                'create_car_reservations',
                'edit_car_reservations',
                'delete_car_reservations',
                'confirm_car_reservations',
                'cancel_car_reservations',
                'update_car_reservation_payment',
                'mark_car_as_sold',
                'view_car_reservation_statistics'
            ]);
        }

        // Get staff role (if exists)
        $staffRole = Role::where('name', 'staff')->first();

        if ($staffRole) {
            // Give limited permissions to staff
            $staffRole->givePermissionTo([
                'view_car_reservations',
                'create_car_reservations',
                'edit_car_reservations',
                'confirm_car_reservations',
                'update_car_reservation_payment'
            ]);
        }

        // Get manager role (if exists)
        $managerRole = Role::where('name', 'manager')->first();

        if ($managerRole) {
            // Give most permissions to manager
            $managerRole->givePermissionTo([
                'view_car_reservations',
                'create_car_reservations',
                'edit_car_reservations',
                'delete_car_reservations',
                'confirm_car_reservations',
                'cancel_car_reservations',
                'update_car_reservation_payment',
                'mark_car_as_sold',
                'view_car_reservation_statistics'
            ]);
        }

        $this->command->info('Car reservation permissions have been created and assigned successfully.');
    }
}
