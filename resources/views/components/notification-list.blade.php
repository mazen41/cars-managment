@props(['notifications'])

@php
$notificationShowDesign = get_setting('notification_show_type');
$notifyImageDesign = '';

if($notificationShowDesign != 'only_text'){
    if($notificationShowDesign == 'design_2'){
        $notifyImageDesign = 'rounded-1';
    }
    elseif($notificationShowDesign == 'design_3'){
        $notifyImageDesign = 'rounded-circle';
    }
}
@endphp

@forelse($notifications as $notification)
<li class="list-group-item d-flex justify-content-between align-items-start py-3">
    <div class="media text-inherit w-100">
        <div class="media-body">
            @php
            $user_type = auth()->user()->user_type;
            $notificationType = get_notification_type($notification->notification_type_id, 'id');
            $notifyContent = $notificationType ? $notificationType->getTranslation('default_text') : '';
            $image = $notificationType ? $notificationType->image : null;
            @endphp
            <div class="d-flex">
                @if($notificationShowDesign != 'only_text')
                <div class="size-35px mr-2 flex-shrink-0">
                    <img src="{{ $image ? uploaded_asset($image) : static_asset('assets/img/notification.png') }}"
                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/notification.png') }}';"
                        class="img-fit h-100 {{ $notifyImageDesign }}">
                </div>
                @endif
                <div class="flex-grow-1">
                    {{-- Order Related Notifications --}}
                    @if ($notification->type == 'App\Notifications\OrderNotification')
                        @php
                        $orderCode = $notification->data['order_code'] ?? 'N/A';
                        $orderCode = "<span class='text-blue'>".$orderCode."</span>";
                        $notifyContent = str_replace('[[order_code]]', $orderCode, $notifyContent);
                        @endphp

                    {{-- Shop Verification Related Notifications --}}
                    @elseif ($notification->type == 'App\Notifications\ShopVerificationNotification')
                        @php
                        if(isset($notification->data['status']) && $notification->data['status'] == 'submitted'){
                            $shopName = "<span class='text-blue'>".($notification->data['name'] ?? 'N/A')."</span>";
                            $notifyContent = str_replace('[[shop_name]]', $shopName, $notifyContent);
                        }
                        @endphp

                    {{-- Shop Product Related Notifications --}}
                    @elseif ($notification->type == 'App\Notifications\ShopProductNotification')
                        @php
                        $productName = "<span class='text-blue'>".($notification->data['name'] ?? 'N/A')."</span>";
                        $notifyContent = str_replace('[[product_name]]', $productName, $notifyContent);
                        @endphp

                    {{-- Seller Payout Notifications --}}
                    @elseif ($notification->type == 'App\Notifications\PayoutNotification')
                        @php
                        $amount = single_price($notification->data['payment_amount'] ?? 0);
                        $shopName = "<span class='text-blue'>".($notification->data['name'] ?? 'N/A')."</span>";
                        $notifyContent = str_replace('[[shop_name]]', $shopName, $notifyContent);
                        $notifyContent = str_replace('[[amount]]', $amount, $notifyContent);
                        @endphp

                    @elseif ($notification->type == 'App\Notifications\SupportTicketNotification')
                        @php
                        $route = route('support_ticket.admin_show', encrypt($notification->data['ticket_id'] ?? 0));
                        $ticketCode = "<a href='".$route."'>".($notification->data['ticket_code'] ?? 'N/A')."</a>";
                        $notifyContent = str_replace('[[ticket_code]]', $ticketCode, $notifyContent);
                        @endphp

                    @elseif ($notification->type == 'App\Notifications\ConversationNotification')
                        @php
                        $route = $user_type == 'admin' ? 
                            route('conversations.admin_show', encrypt($notification->data['conversation_id'] ?? 0)) :
                            route('seller.conversations.show', encrypt($notification->data['conversation_id'] ?? 0));
                        $username = "<a href='".$route."'>".($notification->data['user_name'] ?? 'N/A')."</a>";
                        $notifyContent = str_replace('[[username]]', $username, $notifyContent);
                        @endphp

                    @else
                        @php
                        $notificationData = $notification->data;
                        if(isset($notificationData["className"])){
                            try {
                                $notificationClass = new $notificationData["className"]($notificationData["type"], $notificationData["data"]);
                                $notifyContent = $notificationClass->getBody();
                            } catch (\Exception $e) {
                                $notifyContent = "Error loading notification";
                            }
                        } else {
                            $notifyContent = $notifyContent ?: "New notification";
                        }
                        @endphp
                    @endif

                    <a href="{{ ($user_type == 'admin' || $user_type == 'staff') ?
                                    route('admin.notification.read-and-redirect', encrypt($notification->id)) :
                                    route('seller.notification.read-and-redirect', encrypt($notification->id)) }}"
                       class="text-reset">
                        <p class="mb-1 text-truncate-2">
                            {!! $notifyContent !!}
                        </p>
                        <small class="text-muted">
                            {{ $notification->created_at->diffForHumans() }}
                        </small>
                    </a>
                </div>
            </div>
        </div>
    </div>
</li>
@empty
<li class="list-group-item">
    <div class="py-4 text-center fs-16">
        {{ translate('No notification found') }}
    </div>
</li>
@endforelse
