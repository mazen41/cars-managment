<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NotificationType;
use App\Models\NotificationTypeTranslation;

class AuctionNotificationTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $notificationTypes = [
            // Bidder notifications
            [
                'user_type' => 'customer',
                'type' => 'auction_outbid',
                'name' => 'Outbid Notification',
                'default_text' => 'You have been outbid on auction "[[auction_title]]". Current highest bid: [[current_bid]]',
                'status' => 1,
                'translations' => [
                    'en' => [
                        'name' => 'Outbid Notification',
                        'default_text' => 'You have been outbid on auction "[[auction_title]]". Current highest bid: [[current_bid]]'
                    ],
                    'ar' => [
                        'name' => 'إشعار المزايدة الأعلى',
                        'default_text' => 'تم تجاوز مزايدتك في المزاد "[[auction_title]]". أعلى مزايدة حالية: [[current_bid]]'
                    ]
                ]
            ],
            [
                'user_type' => 'customer',
                'type' => 'auction_ending_soon',
                'name' => 'Auction Ending Soon',
                'default_text' => 'Auction "[[auction_title]]" is ending in [[time_remaining]]. Current bid: [[current_bid]]',
                'status' => 1,
                'translations' => [
                    'en' => [
                        'name' => 'Auction Ending Soon',
                        'default_text' => 'Auction "[[auction_title]]" is ending in [[time_remaining]]. Current bid: [[current_bid]]'
                    ],
                    'ar' => [
                        'name' => 'المزاد ينتهي قريباً',
                        'default_text' => 'المزاد "[[auction_title]]" ينتهي خلال [[time_remaining]]. المزايدة الحالية: [[current_bid]]'
                    ]
                ]
            ],
            [
                'user_type' => 'customer',
                'type' => 'auction_won',
                'name' => 'Auction Won',
                'default_text' => 'Congratulations! You won auction "[[auction_title]]" with bid [[winning_bid]]',
                'status' => 1,
                'translations' => [
                    'en' => [
                        'name' => 'Auction Won',
                        'default_text' => 'Congratulations! You won auction "[[auction_title]]" with bid [[winning_bid]]'
                    ],
                    'ar' => [
                        'name' => 'فوز في المزاد',
                        'default_text' => 'تهانينا! لقد فزت في المزاد "[[auction_title]]" بمزايدة [[winning_bid]]'
                    ]
                ]
            ],
            [
                'user_type' => 'customer',
                'type' => 'auction_ended_no_winner',
                'name' => 'Auction Ended - No Winner',
                'default_text' => 'Auction "[[auction_title]]" has ended without meeting the reserve price',
                'status' => 1,
                'translations' => [
                    'en' => [
                        'name' => 'Auction Ended - No Winner',
                        'default_text' => 'Auction "[[auction_title]]" has ended without meeting the reserve price'
                    ],
                    'ar' => [
                        'name' => 'انتهى المزاد - بدون فائز',
                        'default_text' => 'انتهى المزاد "[[auction_title]]" دون الوصول للسعر المطلوب'
                    ]
                ]
            ],
            [
                'user_type' => 'customer',
                'type' => 'auction_time_extended',
                'name' => 'Auction Time Extended',
                'default_text' => 'Auction "[[auction_title]]" has been extended by [[extension_time]]. New end time: [[new_end_time]]',
                'status' => 1,
                'translations' => [
                    'en' => [
                        'name' => 'Auction Time Extended',
                        'default_text' => 'Auction "[[auction_title]]" has been extended by [[extension_time]]. New end time: [[new_end_time]]'
                    ],
                    'ar' => [
                        'name' => 'تمديد وقت المزاد',
                        'default_text' => 'تم تمديد المزاد "[[auction_title]]" لمدة [[extension_time]]. وقت الانتهاء الجديد: [[new_end_time]]'
                    ]
                ]
            ],

            // Seller notifications
            [
                'user_type' => 'seller',
                'type' => 'auction_new_bid',
                'name' => 'New Bid on Your Auction',
                'default_text' => 'New bid of [[bid_amount]] placed on your auction "[[auction_title]]"',
                'status' => 1,
                'translations' => [
                    'en' => [
                        'name' => 'New Bid on Your Auction',
                        'default_text' => 'New bid of [[bid_amount]] placed on your auction "[[auction_title]]"'
                    ],
                    'ar' => [
                        'name' => 'مزايدة جديدة على مزادك',
                        'default_text' => 'مزايدة جديدة بقيمة [[bid_amount]] على مزادك "[[auction_title]]"'
                    ]
                ]
            ],
            [
                'user_type' => 'seller',
                'type' => 'auction_ended_seller',
                'name' => 'Your Auction Ended',
                'default_text' => 'Your auction "[[auction_title]]" has ended. Final price: [[final_price]]',
                'status' => 1,
                'translations' => [
                    'en' => [
                        'name' => 'Your Auction Ended',
                        'default_text' => 'Your auction "[[auction_title]]" has ended. Final price: [[final_price]]'
                    ],
                    'ar' => [
                        'name' => 'انتهى مزادك',
                        'default_text' => 'انتهى مزادك "[[auction_title]]". السعر النهائي: [[final_price]]'
                    ]
                ]
            ],
            [
                'user_type' => 'seller',
                'type' => 'auction_request_approved',
                'name' => 'Auction Request Approved',
                'default_text' => 'Your auction request for "[[car_title]]" has been approved and auction created',
                'status' => 1,
                'translations' => [
                    'en' => [
                        'name' => 'Auction Request Approved',
                        'default_text' => 'Your auction request for "[[car_title]]" has been approved and auction created'
                    ],
                    'ar' => [
                        'name' => 'تمت الموافقة على طلب المزاد',
                        'default_text' => 'تمت الموافقة على طلب المزاد لسيارة "[[car_title]]" وتم إنشاء المزاد'
                    ]
                ]
            ],
            [
                'user_type' => 'seller',
                'type' => 'auction_request_rejected',
                'name' => 'Auction Request Rejected',
                'default_text' => 'Your auction request for "[[car_title]]" has been rejected. Reason: [[rejection_reason]]',
                'status' => 1,
                'translations' => [
                    'en' => [
                        'name' => 'Auction Request Rejected',
                        'default_text' => 'Your auction request for "[[car_title]]" has been rejected. Reason: [[rejection_reason]]'
                    ],
                    'ar' => [
                        'name' => 'تم رفض طلب المزاد',
                        'default_text' => 'تم رفض طلب المزاد لسيارة "[[car_title]]". السبب: [[rejection_reason]]'
                    ]
                ]
            ],

            // Admin notifications
            [
                'user_type' => 'admin',
                'type' => 'auction_new_request',
                'name' => 'New Auction Request',
                'default_text' => 'New auction request received for car "[[car_title]]" from seller [[seller_name]]',
                'status' => 1,
                'translations' => [
                    'en' => [
                        'name' => 'New Auction Request',
                        'default_text' => 'New auction request received for car "[[car_title]]" from seller [[seller_name]]'
                    ],
                    'ar' => [
                        'name' => 'طلب مزاد جديد',
                        'default_text' => 'تم استلام طلب مزاد جديد لسيارة "[[car_title]]" من البائع [[seller_name]]'
                    ]
                ]
            ],
            [
                'user_type' => 'admin',
                'type' => 'auction_high_activity',
                'name' => 'High Auction Activity',
                'default_text' => 'Auction "[[auction_title]]" has high bidding activity with [[bid_count]] bids',
                'status' => 1,
                'translations' => [
                    'en' => [
                        'name' => 'High Auction Activity',
                        'default_text' => 'Auction "[[auction_title]]" has high bidding activity with [[bid_count]] bids'
                    ],
                    'ar' => [
                        'name' => 'نشاط مزاد عالي',
                        'default_text' => 'المزاد "[[auction_title]]" يشهد نشاط مزايدة عالي مع [[bid_count]] مزايدة'
                    ]
                ]
            ]
        ];

        foreach ($notificationTypes as $typeData) {
            $translations = $typeData['translations'];
            unset($typeData['translations']);

            // Create or update notification type
            $notificationType = NotificationType::updateOrCreate(
                ['type' => $typeData['type'], 'user_type' => $typeData['user_type']],
                $typeData
            );

            // Create translations
            foreach ($translations as $lang => $translation) {
                NotificationTypeTranslation::updateOrCreate(
                    [
                        'notification_type_id' => $notificationType->id,
                        'lang' => $lang
                    ],
                    $translation
                );
            }
        }

        $this->command->info('Auction notification types seeded successfully!');
    }
}