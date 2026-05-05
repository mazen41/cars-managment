<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Conversation;
use App\Http\Resources\V2\ConversationCollection;
use App\Http\Resources\V2\MessageCollection;
use App\Http\Requests\Api\V2\CreateConversationRequest;
use App\Http\Requests\Api\V2\SendMessageRequest;
use App\Mail\ConversationMailManager;
use App\Models\Message;
use App\Models\User;
use App\Notifications\ConversationNotification;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class ChatController extends Controller
{
    public function conversations(): ConversationCollection
    {
        $userId = auth('api')->id();
        $conversations = Conversation::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->latest('id')
            ->paginate(10);

        return new ConversationCollection($conversations);
    }

    public function receivedConversations(): ConversationCollection
    {
        $conversations = Conversation::where('receiver_id', auth('api')->id())
            ->latest('id')
            ->paginate(10);

        return new ConversationCollection($conversations);
    }

    public function sentConversations(): ConversationCollection
    {
        $conversations = Conversation::where('sender_id', auth('api')->id())
            ->latest('id')
            ->paginate(10);

        return new ConversationCollection($conversations);
    }

    public function messages(int $id): JsonResponse|MessageCollection
    {
        $conversation = Conversation::find($id);

        if (!$conversation) {
            return response()->json([
                'result' => false,
                'message' => translate('Conversation not found')
            ], 404);
        }

        $userId = auth('api')->id();
        if (!$this->userCanAccessConversation($conversation, $userId)) {
            return response()->json([
                'result' => false,
                'message' => translate('You are not authorized to access this conversation')
            ], 403);
        }

        $this->markConversationAsViewed($conversation, $userId);

        $messages = $conversation->messages()->latest('id')->paginate(10);
        return new MessageCollection($messages);
    }

    public function sendMessage(SendMessageRequest $request): JsonResponse|MessageCollection
    {
        try {
            $authUser = auth('api')->user();

            // If conversation_id is provided, use existing conversation
            if ($request->conversation_id) {
                return $this->sendToExistingConversation($request, $authUser);
            }

            // Otherwise, find or create conversation
            return $this->findOrCreateConversationAndSend($request, $authUser);

        } catch (Exception $e) {
            \Log::error('Message sending failed', [
                'error' => $e->getMessage(),
                'user_id' => $authUser->id ?? null
            ]);

            return response()->json([
                'result' => false,
                'message' => translate('Failed to send message')
            ], 500);
        }
    }

    private function sendToExistingConversation(SendMessageRequest $request, User $authUser): JsonResponse|MessageCollection
    {
        $conversation = Conversation::findOrFail($request->conversation_id);

        if (!$this->userCanAccessConversation($conversation, $authUser->id)) {
            return response()->json([
                'result' => false,
                'message' => translate('You are not authorized to access this conversation')
            ], 403);
        }

        $message = Message::create([
            'conversation_id' => $request->conversation_id,
            'user_id' => $authUser->id,
            'message' => $request->message
        ]);

        $this->updateConversationViewStatus($conversation, $authUser->id);
        $this->sendNotificationForMessage($conversation, $message, $authUser);
        if($conversation->sender_id == $authUser->id) {
            $receiver = $conversation->receiver;
        } else {
            $receiver = $conversation->sender;
        }
        event(new \App\Events\MessageSent($message, $authUser, $receiver));

        $messages = Message::where('id', $message->id)->paginate(1);
        return new MessageCollection($messages);
    }

    private function findOrCreateConversationAndSend(SendMessageRequest $request, User $authUser): JsonResponse|MessageCollection
    {
        // Determine receiver
        if ($request->receiver_id) {
            $receiver = User::findOrFail($request->receiver_id);
            if ($receiver->id === $authUser->id) {
                return response()->json([
                    'result' => false,
                    'message' => translate('You cannot send a message to yourself')
                ], 422);
            }
        } else {
            // Send to admin
            $receiver = User::where('user_type', 'admin')->firstOrFail();
        }

        // Check for existing conversation
        $conversation = $this->findExistingConversation($authUser, $receiver);

        if (!$conversation) {
            // Create new conversation
            $conversation = Conversation::create([
                'sender_id' => $authUser->id,
                'receiver_id' => $receiver->id,
                'title' => $request->title ?? translate('New Conversation')
            ]);
        }

        // Create message
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $authUser->id,
            'message' => $request->message
        ]);

        $this->updateConversationViewStatus($conversation, $authUser->id);
        $this->sendNotificationForMessage($conversation, $message, $authUser);

        event(new \App\Events\MessageSent($message, $authUser, $receiver));

        $messages = Message::where('id', $message->id)->paginate(1);
        return new MessageCollection($messages);
    }

    private function findExistingConversation(User $user1, User $user2): ?Conversation
    {
        return Conversation::where(function ($query) use ($user1, $user2) {
            $query->where('sender_id', $user1->id)->where('receiver_id', $user2->id);
        })->orWhere(function ($query) use ($user1, $user2) {
            $query->where('sender_id', $user2->id)->where('receiver_id', $user1->id);
        })->first();
    }

    private function sendNotificationForMessage(Conversation $conversation, Message $message, User $sender): void
    {
        if ($conversation->receiver->user_type === 'admin') {
            $notifiables = User::permission('view_all_product_conversations')->get()->toArray();
        } else {
            $notifiables = [$conversation->receiver];
        }

        $this->sendMessageNotification($conversation, $message, $sender, $notifiables);
    }

    public function getNewMessages(int $conversationId, int $lastMessageId): MessageCollection
    {
        // First verify user has access to this conversation
        $conversation = Conversation::findOrFail($conversationId);
        $userId = auth('api')->id();

        if (!$this->userCanAccessConversation($conversation, $userId)) {
            return new MessageCollection(collect([]));
        }

        $messages = Message::where('conversation_id', $conversationId)
            ->where('id', '>', $lastMessageId)
            ->latest('id')
            ->paginate(10);

        return new MessageCollection($messages);
    }
    // Backward compatibility - deprecated, use sendMessage instead
    public function insertMessage(SendMessageRequest $request): JsonResponse|MessageCollection
    {
        return $this->sendMessage($request);
    }

    // Backward compatibility - deprecated, use sendMessage instead
    public function createConversation(CreateConversationRequest $request): JsonResponse
    {
        // Convert CreateConversationRequest to SendMessageRequest format
        $sendMessageRequest = new SendMessageRequest();
        $sendMessageRequest->merge([
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'title' => $request->title
        ]);

        return $this->sendMessage($sendMessageRequest);
    }

    private function sendMessageNotification(Conversation $conversation, Message $message, User $sender, array $notifiables): void
    {
        if ($sender->user_type === 'customer') {
            return;
        }

        $emailData = [
            'view' => 'emails.conversation',
            'subject' => translate('Sender') . ': ' . $sender->name,
            'from' => config('mail.from.address'),
            'content' => translate('Hi! You received a message from ') . $sender->name . '.',
            'sender' => $sender->name,
            'details' => $message->message
        ];

        $notificationData = [
            'user_id' => $sender->id,
            'user_name' => $sender->name,
            'conversation_id' => $conversation->id,
            'notification_type_id' => $this->getNotificationTypeId($sender->user_type)
        ];

        Notification::send($notifiables, new ConversationNotification($notificationData));

        if ($conversation->receiver->email) {
            try {
                Mail::to($conversation->receiver->email)->queue(new ConversationMailManager($emailData));
            } catch (Exception $e) {
                \Log::warning('Failed to send conversation email', [
                    'conversation_id' => $conversation->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    public function count(): JsonResponse
    {
        $userId = auth('api')->id();
        $count = Conversation::where(function ($query) use ($userId) {
            $query->where('sender_id', $userId)->where('sender_viewed', 0);
        })->orWhere(function ($query) use ($userId) {
            $query->where('receiver_id', $userId)->where('receiver_viewed', 0);
        })->count();

        return response()->json([
            'result' => true,
            'count' => $count
        ]);
    }

    // Helper methods
    private function userCanAccessConversation(Conversation $conversation, int $userId): bool
    {
        return $conversation->sender_id === $userId || $conversation->receiver_id === $userId;
    }

    private function markConversationAsViewed(Conversation $conversation, int $userId): void
    {
        if ($conversation->sender_id === $userId) {
            $conversation->update(['sender_viewed' => 1]);
        } elseif ($conversation->receiver_id === $userId) {
            $conversation->update(['receiver_viewed' => 1]);
        }
    }

    private function updateConversationViewStatus(Conversation $conversation, int $userId): void
    {
        if ($conversation->sender_id === $userId) {
            $conversation->update(['receiver_viewed' => 0]);
        } elseif ($conversation->receiver_id === $userId) {
            $conversation->update(['sender_viewed' => 0]);
        }
    }

    private function getNotificationTypeId(string $userType): int
    {
        $notificationType = $userType === 'admin'
            ? 'conversation_new_admin'
            : 'conversation_new_seller';

        return get_notification_type($notificationType, 'type')->id;
    }

    private function buildConversationResponse(Conversation $conversation, string $message): JsonResponse
    {
        $receiver = $conversation->receiver;
        $isAdmin = $receiver->user_type === 'admin';

        return response()->json([
            'result' => true,
            'conversation_id' => $conversation->id,
            'receiver_name' => $isAdmin ? config('app.name') : $receiver->name,
            'receiver_image' => $isAdmin
                ? uploaded_asset(get_setting('header_logo'))
                : uploaded_asset($receiver->shop->logo ?? $receiver->avatar_original),
            'title' => $conversation->title,
            'message' => $message,
        ]);
    }
}
