<?php

namespace App\Http\Controllers\Api\V2;
use App\Http\Resources\V2\NotificationCollection;
use DB;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function allNotification()
    {
        auth()->user()->unreadNotifications->markAsRead();
        $notifications = auth()->user()->notifications()->get();
        return new NotificationCollection($notifications);
    }

    public function unreadNotifications(){
        $notifications = auth()->user()->unreadNotifications()->get();
        return response()->json([
            'count' => $notifications->count(),
            'data' => new NotificationCollection($notifications),
        ]);
    }

    public function bulkDelete(Request $request){
        $request->validate([
            'notification_ids' => 'required|array',
        ]);
        try{
            DB::beginTransaction();
            auth()->user()->notifications()->whereIn('id',$request->notification_ids)->delete();
            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            return  $this->failed(translate('Something went wrong'));
        }
        return $this->success(translate('Notification deleted successfully'));
    }

    public function notificationMarkAsRead($notificationId) {

        // Notification mark as read
        auth()->user()->unreadNotifications->where('id',$notificationId)->markAsRead();

        return response()->json([
            'result' => true,
            'message' => translate('Notification marked as read successfully')
        ]);
    }

}
