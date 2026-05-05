<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes to auctions table
        $this->addIndexIfNotExists('auctions', ['seller_id', 'status'], 'idx_auctions_seller_status');
        $this->addIndexIfNotExists('auctions', ['car_id', 'status'], 'idx_auctions_car_status');
        $this->addIndexIfNotExists('auctions', ['current_bidder_id', 'status'], 'idx_auctions_current_bidder_status');
        $this->addIndexIfNotExists('auctions', ['winner_id', 'status'], 'idx_auctions_winner_status');
        $this->addIndexIfNotExists('auctions', ['status', 'created_at'], 'idx_auctions_status_created');
        $this->addIndexIfNotExists('auctions', ['status', 'current_bid'], 'idx_auctions_status_bid');
        $this->addIndexIfNotExists('auctions', ['auto_extend_enabled', 'status', 'end_time'], 'idx_auctions_auto_extend');

        // Add indexes to auction_bids table
        $this->addIndexIfNotExists('auction_bids', ['auction_id', 'is_valid', 'amount'], 'idx_bids_auction_valid_amount');
        $this->addIndexIfNotExists('auction_bids', ['auction_id', 'is_valid', 'bid_time'], 'idx_bids_auction_valid_time');
        $this->addIndexIfNotExists('auction_bids', ['user_id', 'is_valid', 'bid_time'], 'idx_bids_user_valid_time');
        $this->addIndexIfNotExists('auction_bids', ['auction_id', 'user_id', 'is_valid'], 'idx_bids_auction_user_valid');
        $this->addIndexIfNotExists('auction_bids', ['auction_id', 'is_valid', 'created_at'], 'idx_bids_auction_valid_created');
        $this->addIndexIfNotExists('auction_bids', ['is_winning', 'is_valid'], 'idx_bids_winning_valid');
        $this->addIndexIfNotExists('auction_bids', ['created_at', 'is_valid'], 'idx_bids_created_valid');

        // Add indexes to auction_requests table
        $this->addIndexIfNotExists('auction_requests', ['seller_id', 'status'], 'idx_requests_seller_status');
        $this->addIndexIfNotExists('auction_requests', ['car_id', 'status'], 'idx_requests_car_status');
        $this->addIndexIfNotExists('auction_requests', ['admin_id', 'status'], 'idx_requests_admin_status');
        $this->addIndexIfNotExists('auction_requests', ['status', 'created_at'], 'idx_requests_status_created');
        $this->addIndexIfNotExists('auction_requests', ['status', 'reviewed_at'], 'idx_requests_status_reviewed');

        // Add indexes to cars table for auction-related queries
        $this->addIndexIfNotExists('cars', ['user_id', 'status'], 'idx_cars_user_status');
        $this->addIndexIfNotExists('cars', ['status', 'created_at'], 'idx_cars_status_created');

        // Add indexes to users table for auction participants
        $this->addIndexIfNotExists('users', ['user_type', 'email_verified_at'], 'idx_users_type_verified');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $indexes = [
            'auctions' => [
                'idx_auctions_seller_status',
                'idx_auctions_car_status',
                'idx_auctions_current_bidder_status',
                'idx_auctions_winner_status',
                'idx_auctions_status_created',
                'idx_auctions_status_bid',
                'idx_auctions_auto_extend'
            ],
            'auction_bids' => [
                'idx_bids_auction_valid_amount',
                'idx_bids_auction_valid_time',
                'idx_bids_user_valid_time',
                'idx_bids_auction_user_valid',
                'idx_bids_auction_valid_created',
                'idx_bids_winning_valid',
                'idx_bids_created_valid'
            ],
            'auction_requests' => [
                'idx_requests_seller_status',
                'idx_requests_car_status',
                'idx_requests_admin_status',
                'idx_requests_status_created',
                'idx_requests_status_reviewed'
            ],
            'cars' => [
                'idx_cars_user_status',
                'idx_cars_status_created'
            ],
            'users' => [
                'idx_users_type_verified'
            ]
        ];

        foreach ($indexes as $table => $tableIndexes) {
            foreach ($tableIndexes as $index) {
                if ($this->indexExists($table, $index)) {
                    DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$index}`");
                }
            }
        }
    }

    /**
     * Add index if it doesn't exist
     */
    private function addIndexIfNotExists(string $table, array $columns, string $indexName): void
    {
        if (!$this->indexExists($table, $indexName)) {
            $columnList = implode(', ', array_map(fn($col) => "`{$col}`", $columns));
            DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$indexName}` ({$columnList})");
        }
    }

    /**
     * Check if an index exists on a table
     */
    private function indexExists(string $table, string $index): bool
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index]);
            return !empty($indexes);
        } catch (\Exception $e) {
            return false;
        }
    }
};