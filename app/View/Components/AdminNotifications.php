<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Facades\Cache;

class AdminNotifications extends Component
{
    public $notificationCount;
    public $notificationTypes;

    public function __construct()
    {
        $user = auth()->user();
        
        // Cache notification count for 1 minute
        $this->notificationCount = Cache::remember(
            "admin_notification_count_{$user->id}",
            60,
            fn() => $user->unreadNotifications()->count()
        );

        // Define notification types for tabs
        $this->notificationTypes = [
            'orders' => [
                'label' => 'Orders',
                'type' => 'App\Notifications\OrderNotification',
                'icon' => 'las la-shopping-cart'
            ],
            'cars' => [
                'label' => 'Cars',
                'type' => 'App\Notifications\CarNotification',
                'icon' => 'las la-car'
            ],
            'auction' => [
                'label' => 'Auction',
                'type' => 'App\Notifications\AdminAuctionNotification',
                'icon' => 'las la-gavel'
            ],
            'reservation' => [
                'label' => 'Car Reservations',
                'type' => 'App\Notifications\AdminCarReservationNotification',
                'icon' => 'las la-calendar-check'
            ],
            'inspection' => [
                'label' => 'Car Inspections',
                'type' => 'App\Notifications\AdminCarInspectionNotification',
                'icon' => 'las la-clipboard-check'
            ],
            'sellers' => [
                'label' => 'Sellers',
                'type' => '%shop%',
                'icon' => 'las la-store',
                'like' => true
            ],
            'payouts' => [
                'label' => 'Payouts',
                'type' => 'App\Notifications\PayoutNotification',
                'icon' => 'las la-money-bill'
            ],
            'support' => [
                'label' => 'Support',
                'type' => ['App\Notifications\SupportTicketNotification', 'App\Notifications\ConversationNotification'],
                'icon' => 'las la-headset'
            ]
        ];
    }

    public function render()
    {
        return view('components.admin-notifications');
    }
}
