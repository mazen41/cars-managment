<?php


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/*
|--------------------------------------------------------------------------
| Auction Broadcast Channels
|--------------------------------------------------------------------------
|
| Public channels for auction rooms and items (no authentication required)
| Private channels for user-specific and seller-specific notifications
|
*/

// Public channel for auction room events (no authentication required)
Broadcast::channel('auction-room.{id}', function () {
    return true;
});

// Public channel for auction item events (no authentication required)
Broadcast::channel('auction-item.{id}', function () {
    return true;
});

// Private channel for user-specific notifications (bid rejected, offer updates)
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Private channel for seller-specific notifications (offer received)
Broadcast::channel('seller.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Private channel for admin dashboard (requires admin role)
Broadcast::channel('admin', function ($user) {
    return $user->user_type === 'admin' || $user->user_type === 'staff';
});
