<?php

namespace App\Http\Controllers;

use App\Models\NotificationType;
use App\Models\User;
use App\Notifications\CustomNotification;
use App\Notifications\MobileNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Fetch notifications via AJAX for lazy loading
     */
    public function fetchNotifications(Request $request)
    {
        $user = auth()->user();
        $type = $request->input('type');
        $isLike = $request->input('like', false);
        $limit = $request->input('limit', 20);

        // Build query
        $query = $user->unreadNotifications();

        // Handle different type formats
        try {
            $typeDecoded = json_decode($type, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($typeDecoded)) {
                $type = $typeDecoded;
            }
        } catch (\Exception $e) {
            // Not JSON, use as is
        }

        if (is_array($type)) {
            // Multiple types (e.g., support notifications)
            $query->where(function ($q) use ($type) {
                foreach ($type as $t) {
                    $q->orWhere('type', $t);
                }
            });
        } elseif ($isLike) {
            // LIKE query (e.g., shop notifications)
            $query->where('type', 'like', $type);
        } else {
            // Exact match
            $query->where('type', $type);
        }

        $notifications = $query->take($limit)->get();

        // Render the notification list
        $html = view('components.notification-list', compact('notifications'))->render();

        return response()->json([
            'success' => true,
            'html' => $html,
            'count' => $notifications->count()
        ]);
    }

    /**
     * Admin notifications index page
     */
    public function adminIndex(Request $request)
    {
        $type = $request->get('type', 'all');
        $perPage = 50;

        $query = auth()->user()->notifications()->orderBy('created_at', 'desc');

        if ($type !== 'all') {
            $query->where('type', $type);
        }

        $notifications = $query->paginate($perPage);

        return view('backend.notification.index', compact('notifications', 'type'));
    }

    /**
     * Notification settings page
     */
    public function notificationSettings()
    {
        return view('backend.notification.settings');
    }

    /**
     * Bulk delete notifications (admin)
     */
    public function bulkDeleteAdmin(Request $request)
    {
        $ids = $request->input('notification_ids', []);

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => translate('No notifications selected')]);
        }

        try {
            auth()->user()->notifications()->whereIn('id', $ids)->delete();

            // Clear notification count cache
            Cache::forget("admin_notification_count_" . auth()->id());

            return 1;
        } catch (\Exception $e) {
            \Log::error('Failed to delete notifications: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Custom notification page
     */
    public function customNotification()
    {
        $customNotificationTypes = NotificationType::where('type', 'custom')->where('status', 1)->get();
        return view('backend.notification.custom_notification', compact('customNotificationTypes'));
    }

    /**
     * Send custom notification
     */
    public function sendCustomNotification(Request $request)
    {
        // Validation
        $rules = [
            'notification_type_id' => ['required'],
            'link' => ['nullable', 'max:255'],
            'user_ids' => ['required_without:all_customers', 'array'],
            'all_customers' => ['nullable', 'boolean']
        ];

        $messages = [
            'user_ids.required_without' => translate('Please either select specific customers or check "All Customers"'),
            'user_ids.required' => translate('Select Customers'),
            'notification_type_id.required' => translate('Notification type is required'),
            'link.max' => translate('Link should have max 255 characters')
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        try {
            // Get notification type
            $notification = NotificationType::findOrFail($request->notification_type_id);

            // Prepare notification data
            $data = [
                'link' => $request->link,
                'notification_type_id' => $request->notification_type_id
            ];

            // Get users based on selection
            $users = $request->has('all_customers')
                ? User::where('user_type', 'customer')->get()
                : User::whereIn('id', $request->user_ids)->get();

            // Send notifications in chunks to avoid timeout
            foreach ($users->chunk(100) as $chunk) {
                foreach ($chunk as $user) {
                    try {
                        // Send database notification
                        Notification::send($user, new CustomNotification($data));

                        // Send FCM notification
                        Notification::send($user, new MobileNotification(
                            env('APP_NAME'),
                            $notification->default_text,
                            $data
                        ));
                    } catch (\Exception $e) {
                        \Log::error('Failed to send notification to user ' . $user->id . ': ' . $e->getMessage());
                        continue; // Continue with next user if one fails
                    }
                }
            }

            flash(translate('Notifications have been sent successfully'))->success();
            return back();
        } catch (\Exception $e) {
            \Log::error('Failed to send notifications: ' . $e->getMessage());
            flash(translate('Failed to send notifications'))->error();
            return back();
        }
    }

    /**
     * Shop custom notification page
     */
    public function customShopNotification()
    {
        $customNotificationTypes = NotificationType::where('type', 'custom')->where('status', 1)->get();
        $shops = User::where('user_type', 'seller')
        ->where(function ($query) {
            $query->whereNotNull('email_verified_at')
                  ->orWhereNotNull('phone_verified_at');
        })
        ->where('banned', 0)->get();

    return view('backend.notification.shop_custom_notification', compact('shops', 'customNotificationTypes'));
    }

    /**
     * Send shop custom notification
     */
    public function sendShopCustomNotification(Request $request)
    {
         $rules = [
            'user_ids'              => ['required'],
            'notification_type_id'  => ['required'],
            'link'                  => ['max:255'],
        ];
        $messages = [
            'user_ids.required'             => translate('Select Customers'),
            'notification_type_id.required' => translate('Notification type is required'),
            'link.max'                      => translate('Link should have max 255 characters')
        ];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }
        try {
            // Get notification type
            $notification = NotificationType::findOrFail($request->notification_type_id);

            // Prepare notification data
            $data = [
                'link' => $request->link,
                'notification_type_id' => $request->notification_type_id
            ];

            // Get users based on selection
            $users = $request->has('all_customers')
                ? User::where('user_type', 'customer')->get()
                : User::whereIn('id', $request->user_ids)->get();

            // Send notifications in chunks to avoid timeout
            foreach ($users->chunk(100) as $chunk) {
                foreach ($chunk as $user) {
                    try {
                        // Send database notification
                        Notification::send($user, new CustomNotification($data));

                        // Send FCM notification
                        Notification::send($user, new MobileNotification(
                            env('APP_NAME'),
                            $notification->default_text,
                            $data
                        ));
                    } catch (\Exception $e) {
                        \Log::error('Failed to send notification to user ' . $user->id . ': ' . $e->getMessage());
                        continue; // Continue with next user if one fails
                    }
                }
            }

            flash(translate('Notifications have been sent successfully'))->success();
            return back();
        } catch (\Exception $e) {
            \Log::error('Failed to send notifications: ' . $e->getMessage());
            flash(translate('Failed to send notifications'))->error();
            return back();
        }
    }

    /**
     * Custom notification history
     */
    public function customNotificationHistory()
    {
        $customNotifications = DB::table('notifications')->where('type', 'App\Notifications\CustomNotification')
            ->groupBy(DB::raw('Date(created_at)'), 'notification_type_id')
            ->orderBy('created_at', 'desc')
            ->paginate(13);

        return view('backend.notification.custom_notification_history', compact('customNotifications'));
    }

    /**
     * Mark notification as read and redirect
     */
    public function readAndRedirect($id)
    {
        try {
            $notification = auth()->user()->notifications()->findOrFail(decrypt($id));

            // Mark as read
            $notification->markAsRead();

            // Clear notification count cache
            Cache::forget("admin_notification_count_" . auth()->id());

            // Determine redirect URL based on notification type
            $redirectUrl = $this->getRedirectUrl($notification, auth()->user()->user_type);

            return redirect($redirectUrl);
        } catch (\Exception $e) {
            \Log::error('Failed to read notification: ' . $e->getMessage());
            flash(translate('Notification not found'))->error();
            return redirect()->back();
        }
    }

   /**
 * Get the redirect URL based on notification type and user role.
 * * @param object $notification
 * @param string $userType
 * @return string
 */
protected function getRedirectUrl($notification, $userType)
{
    $data = $notification->data;
    $lang = config('app.locale');

    switch ($notification->type) {
        case 'App\Notifications\OrderNotification':
            $orderId = encrypt($data['order_id']);
            if ($userType == 'admin') return route('all_orders.show', $orderId);
            if ($userType == 'seller') return route('seller.orders.show', $orderId);
            if ($userType == 'customer') return route('purchase_history.details', $orderId);
            break;

        case 'App\Notifications\ShopProductNotification':
            $params = ['id' => $data['id'], 'lang' => $lang];
            if ($userType == 'admin') {
                return $data['type'] == 'physical'
                    ? route('products.seller.edit', $params)
                    : route('digitalproducts.edit', $params);
            }
            if ($userType == 'seller') {
                return $data['type'] == 'physical'
                    ? route('seller.products.edit', $params)
                    : route('seller.digitalproducts.edit', $params);
            }
            break;

        case 'App\Notifications\PayoutNotification':
            $isPending = ($data['status'] == 'pending');
            if ($userType == 'admin') {
                return route($isPending ? 'withdraw_requests_all' : 'sellers.payment_histories');
            }
            return route($isPending ? 'seller.money_withdraw_requests.index' : 'seller.payments.index');

        case 'App\Notifications\ShopVerificationNotification':
            if (in_array($userType, ['admin', 'staff'])) {
                return route('sellers.show_verification_request', $data['id']);
            }
            return route('seller.dashboard');

        case 'App\Notifications\SupportTicketNotification':
            if (in_array($userType, ['admin', 'staff'])) {
                return route('support_ticket.admin_show', encrypt($data['ticket_id']));
            }
            break;

        case 'App\Notifications\ConversationNotification':
            $convId = encrypt($data['conversation_id']);
            if (in_array($userType, ['admin', 'staff'])) {
                return route('conversations.admin_show', $convId);
            }
            return route('seller.conversations.show', $convId);

        case 'App\Notifications\CustomNotification':
            return $data['link'];

        default:
            return $data['data']['admin_url'] ?? $data['data']['url'] ?? url('/admin');
    }

    return url('/'); // Fallback if no conditions are met
}

    /**
     * Show all notifications page
     */
    public function allNotifications(Request $request)
    {
        $type = $request->get('type', 'all');
        $perPage = 50;

        $query = auth()->user()->notifications();

        if ($type !== 'all') {
            $query->where('type', $type);
        }

        $notifications = $query->paginate($perPage);

        return view('backend.notification.index', compact('notifications', 'type'));
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        // Clear notification count cache
        Cache::forget("admin_notification_count_" . auth()->id());

        flash(translate('All notifications marked as read'))->success();
        return redirect()->back();
    }

    public function bulkDeleteCustomer(Request $request)
    {
        $this->bulkDelete($request->all());
        return 1;
    }

    public function bulkDelete($data)
    {
        if ($data['notification_ids']) {
            foreach ($data['notification_ids'] as $notificationId) {
                DB::table('notifications')->where('id', $notificationId)->delete();
            }
        }
    }

     public function nonLinkableNotificationRead()
    {
        $unReadNotifications = auth()->user()->notifications()->where('type', 'App\Notifications\customNotification')->get();
        foreach ($unReadNotifications  as $notification) {
            if ($notification->data['link'] == null) {
                $notification->read_at = date("Y-m-d H:i:s");
                $notification->save();
            }
        }
        return count(auth()->user()->unreadNotifications);
    }

    // Custom Notifications delete
    public function customNotificationSingleDelete($identifier)
    {
        $this->customNotificationDelete($identifier);
        flash(translate('Custom notification deleted successfully'))->success();
        return back();
    }

    public function customNotificationBulkDelete(Request $request)
    {
        if ($request->identifiers != null) {
            foreach ($request->identifiers as $identifier) {
                $this->customNotificationDelete($identifier);
            }
        }
        return 1;
    }

    public function customNotificationDelete($identifier)
    {
        $var = explode("_", $identifier);
        $type = $var[0];
        $created_at = date('Y-m-d', strtotime($var[1]));
        DB::table('notifications')->where('notification_type_id', $type)->where(DB::raw('Date(created_at)'), $created_at)->delete();
    }
    // Custom Notifications delete end

    public function customNotifiedCustomersList(Request $request)
    {
        $var = explode("_", $request->identifier);
        $type = $var[0];
        $created_at = date('Y-m-d', strtotime($var[1]));
        $notifications = DB::table('notifications')->where('notification_type_id', $type)->where(DB::raw('Date(created_at)'), $created_at)->get();
        $notificationType = get_notification_type($notifications[0]->notification_type_id, 'id');
        $content = $notificationType->getTranslation('default_text');
        $notificationData = json_decode($notifications[0]->data, true);
        $link = json_decode($notifications[0]->data, true)['link'];
        return view('backend.notification.custom_notified_customers_list', compact('notifications', 'content', 'link'));
    }
}
