<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AuctionPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create auction management permissions
        $auctionPermissions = [
            // Auction Rooms
            'view_auction_rooms',
            'create_auction_room',
            'edit_auction_room',
            'start_auction_room',
            'cancel_auction_room',
            'monitor_auction_room',
            'manage_auction_items',

            // Auction Listing Requests
            'view_auction_listing_requests',
            'approve_auction_listing_request',
            'reject_auction_listing_request',
            'bulk_manage_listing_requests',

            // Auction Offers
            'view_auction_offers',
            'force_accept_auction_offer',
            'force_reject_auction_offer',

            // Auction Monitoring & Analytics
            'view_auction_dashboard',
            'view_auction_audit_logs',
            'export_auction_audit_logs',
            'view_auction_system_status',
            'view_auction_analytics',

            // Auction Reports
            'view_auction_reports',

            // Auction Invoices
            'view_auction_invoices',
            'manage_auction_invoices',
            'download_auction_invoices',
            'export_auction_invoices',

            // Insurance deposits
            'view_insurance_deposits',
            'manage_insurance_deposits',

            // Auction settings
            'manage_auction_settings'
        ];

        foreach ($auctionPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission], ['section' => 'auction'] );
        }

        // Assign all permissions to Super Admin role
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($auctionPermissions);
        }

        // Assign all permissions to Tech Support role
        $techSupportRole = Role::where('name', 'Tech Support')->first();
        if ($techSupportRole) {
            $techSupportRole->givePermissionTo($auctionPermissions);
        }

        // Assign view-only permissions to Admin role
        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $viewPermissions = array_filter($auctionPermissions, function($permission) {
                return str_starts_with($permission, 'view_');
            });
            $adminRole->givePermissionTo($viewPermissions);
        }

        $this->command->info('Auction management permissions created and assigned successfully.');
    }
}
