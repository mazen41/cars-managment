<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BusinessSetting;

class AuctionConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $auctionSettings = [
            // Basic Configuration
            'auction_default_bid_increment' => '100.00',
            'auction_default_duration_hours' => '24',
            'auction_auto_extend_enabled' => '1',
            'auction_auto_extend_duration' => '120',
            'auction_max_extensions' => '5',
            'auction_bid_rate_limit' => '10',
            'auction_ending_notification_minutes' => '10',
            'auction_require_verification' => '0',
            'auction_allow_seller_requests' => '1',

            // Notification Templates
            'auction_bid_placed_template' => 'A new bid of {bid_amount} has been placed on {auction_title} by {user_name}.',
            'auction_outbid_template' => 'You have been outbid on {auction_title}. The current highest bid is {current_bid}. Place a new bid to stay in the auction!',
            'auction_ending_template' => 'The auction for {auction_title} is ending soon! Only {time_remaining} left. Current bid: {current_bid}',
            'auction_ended_template' => 'The auction for {auction_title} has ended. Final winning bid: {final_price} by {winner_name}.',
            'auction_won_template' => 'Congratulations {user_name}! You have won the auction for {auction_title} with a bid of {final_price}. Please proceed with payment to complete your purchase.',
            'auction_time_extended_template' => 'The auction for {auction_title} has been extended by {extension_time} due to last-minute bidding activity.',
        ];

        foreach ($auctionSettings as $type => $value) {
            BusinessSetting::updateOrCreate(
                ['type' => $type],
                ['value' => $value]
            );
        }

        $this->command->info('Auction configuration settings seeded successfully!');
    }
}