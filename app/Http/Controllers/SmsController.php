<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Jobs\SendSmsToUser;
use Illuminate\Support\Facades\Bus;

class SmsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:send_bulk_sms'])->only('index');
    }

    public function index()
    {
        return view('backend.otp_systems.sms.index');
    }

    public function send(Request $request)
    {
        try {
            $all_customers = $request->boolean('all_customers');
            $userQuery = User::query()
                ->where('user_type', 'customer')
                ->whereNotNull('phone')
                ->whereNotNull('phone_verified_at');

            if (!$all_customers) {
                $userQuery->whereIn('id', $request->input('user_ids', []));
            }

            if (!$userQuery->exists()) {
                flash(translate('No user found.'))->error();
                return redirect()->back();
            }

            $userQuery->select(['id', 'phone'])->chunk(100, function ($users) use ($request) {
                foreach ($users as $user) {
                    SendSmsToUser::dispatch($user->id, $request->content, $request->template_id);
                }
            });

            flash(translate('SMS has been sent.'))->success();
            return redirect()->back();

        } catch (\Exception $e) {
            \Log::error('SMS dispatch error: ' . $e->getMessage());
            flash(translate('Error sending SMS.'))->error();
            return redirect()->back();
        }
    }
}
