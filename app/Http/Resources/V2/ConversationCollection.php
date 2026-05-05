<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ConversationCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($conversation) {
                $currentUserId = auth()->id();
                $otherParticipant = $this->getOtherParticipant($conversation, $currentUserId);
                $isAdmin = $otherParticipant->user_type === 'admin';
                
                return [
                    'id' => $conversation->id,
                    'receiver_id' => (int) $otherParticipant->id,
                    'receiver_type' => $otherParticipant->user_type,
                    'shop_id' => $isAdmin ? 0 : ($otherParticipant->shop->id ?? 0),
                    'receiver_name' => $isAdmin ? config('app.name') : ($otherParticipant->shop->name ?? $otherParticipant->name),
                    'receiver_image' => $isAdmin 
                        ? uploaded_asset(get_setting('header_logo')) 
                        : uploaded_asset($otherParticipant->shop->logo ?? $otherParticipant->avatar_original),
                    'title' => $conversation->title,
                    'sender_viewed' => (int) $conversation->sender_viewed,
                    'receiver_viewed' => (int) $conversation->receiver_viewed,
                    'is_seen' => !$this->hasUnseenMessages($conversation, $currentUserId),
                    'last_message' => $conversation->messages()->latest()->first()?->message,
                    'date' => $conversation->updated_at,
                ];
            })
        ];
    }

    private function hasUnseenMessages($conversation, int $currentUserId): bool
    {
        return ($currentUserId === $conversation->sender_id && $conversation->sender_viewed == 0) ||
               ($currentUserId === $conversation->receiver_id && $conversation->receiver_viewed == 0);
    }

    private function getOtherParticipant($conversation, int $currentUserId)
    {
        return $currentUserId === $conversation->sender_id 
            ? $conversation->receiver 
            : $conversation->sender;
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
