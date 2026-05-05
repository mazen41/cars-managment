<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\BusinessSetting;
use App\Models\Message;
use App\Models\Product;
use Auth;
use Mail;
use App\Mail\ConversationMailManager;
use App\Notifications\ConversationNotification;
use Illuminate\Support\Facades\Notification;
use App\Http\Resources\V2\MessageCollection;

class ConversationController extends Controller
{
    public function __construct()
    {
        // Staff Permission Check
        $this->middleware(['permission:view_all_product_conversations'])->only(['admin_index', 'get_new_messages']);
        $this->middleware(['permission:delete_product_conversations'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (BusinessSetting::where('type', 'conversation_system')->first()->value == 1) {
            $conversations = Conversation::where('sender_id', Auth::user()->id)->orWhere('receiver_id', Auth::user()->id)->orderBy('updated_at', 'desc')->paginate(8);
            return view('frontend.user.conversations.index', compact('conversations'));
        } else {
            flash(translate('Conversation is disabled at this moment'))->warning();
            return back();
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function admin_index()
    {
        $admins = \App\Models\User::where('user_type', 'admin')->get()->flatMap(function ($admin) {
            return collect($admin->id);
        });
        if (BusinessSetting::where('type', 'conversation_system')->first()->value == 1) {
            $conversations = Conversation::whereIn('receiver_id', $admins)->orderBy('updated_at', 'desc')->paginate(15);
            return view('backend.support.conversations.index', compact('conversations'));
        } else {
            flash(translate('Conversation is disabled at this moment'))->warning();
            return back();
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $store = $product->user;
        $user_type = $store->user_type;
        $user_id = Auth::user()->id;
        $notifiables = array();
        array_push($notifiables, $store);

        if ($user_type == 'admin') {
            $all_admin_notifiables = \App\Models\User::permission(['view_all_support_tickets', 'view_all_product_conversations', 'view_all_product_queries'])->get();

            foreach ($all_admin_notifiables as $notifiable) {
                array_push($notifiables, $notifiable);
            }
        }

        $existing_conversation = Conversation::where('sender_id', $user_id)
            ->where('receiver_id', $store->id)
            ->whereHasMorph(
                'conversable',
                [Product::class],
                function ($query) use ($request) {
                    $query->where('conversable_id', $request->product_id);
                }
            )->first();

        if ($existing_conversation) {
            $message = new Message;
            $message->conversation_id = $existing_conversation->id;
            $message->user_id = $user_id;
            $message->message = $request->message;
            if ($message->save()) {

                $this->send_message_to_seller($existing_conversation, $message, $user_type, $notifiables);
            }
        } else {
            $conversation = new Conversation;
            $conversation->sender_id = Auth::user()->id;
            $conversation->receiver_id = $store->id;
            $conversation->title = $request->title;
            $conversation->conversable()->associate($product);
            if ($conversation->save()) {
                $message = new Message;
                $message->conversation_id = $conversation->id;
                $message->user_id = Auth::user()->id;
                $message->message = $request->message;

                if ($message->save()) {

                    $this->send_message_to_seller($conversation, $message, $user_type, $notifiables);
                }
            }
        }

        flash(translate('Message has been sent to seller'))->success();
        return back();
    }

    public function send_message_to_seller($conversation, $message, $user_type, $notifiables)
    {
        $array['view'] = 'emails.conversation';
        $array['subject'] = translate('Sender').':- '. Auth::user()->name;
        $array['from'] = env('MAIL_FROM_ADDRESS');
        $array['content'] = translate('Hi! You recieved a message from ') . Auth::user()->name . '.';
        $array['sender'] = Auth::user()->name;

        $data = array();
        $data['user_id']   = Auth::user()->id;
        $data['user_name']  = Auth::user()->name;
        $data['conversation_id'] = $conversation->id;

        if ($user_type == 'admin') {
            $array['link'] = route('conversations.admin_show', encrypt($conversation->id));
            $data['notification_type_id'] = get_notification_type('conversation_new_admin', 'type')->id;
        } else {
            $array['link'] = route('conversations.show', encrypt($conversation->id));
            $data['notification_type_id'] = get_notification_type('conversation_new_seller', 'type')->id;
        }
        $array['details'] = $message->message;
        Notification::send($notifiables, new ConversationNotification($data));
        try {
            Mail::to($notifiables)->queue(new ConversationMailManager($array));
        } catch (\Exception $e) {
            //dd($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $conversation = Conversation::findOrFail(decrypt($id));
        if ($conversation->sender_id == Auth::user()->id) {
            $conversation->sender_viewed = 1;
        } elseif ($conversation->receiver_id == Auth::user()->id) {
            $conversation->receiver_viewed = 1;
        }
        $conversation->save();
        return view('frontend.user.conversations.show', compact('conversation'));
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function refresh(Request $request)
    {
        $conversation = Conversation::findOrFail(decrypt($request->id));
        if ($conversation->sender_id == Auth::user()->id) {
            $conversation->sender_viewed = 1;
            $conversation->save();
        } else {
            $conversation->receiver_viewed = 1;
            $conversation->save();
        }
        return view('frontend.partials.messages', compact('conversation'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function admin_show($id)
    {
        $conversation = Conversation::findOrFail(decrypt($id));
        if ($conversation->sender_id == Auth::user()->id) {
            $conversation->sender_viewed = 1;
        } elseif ($conversation->receiver_id == Auth::user()->id) {
            $conversation->receiver_viewed = 1;
        }
        $conversation->save();
        return view('backend.support.conversations.show', compact('conversation'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $conversation = Conversation::findOrFail(decrypt($id));
        $conversation->messages()->delete();

        if (Conversation::destroy(decrypt($id))) {
            flash(translate('Conversation has been deleted successfully'))->success();
            return back();
        }
    }

    public function get_new_messages($conversation_id, $last_message_id)
    {
        $messages = Message::where('conversation_id', $conversation_id)
            ->where('id', '>', $last_message_id)
            ->orderBy('id', 'asc')
            ->get();

        return new MessageCollection($messages);
    }
}
