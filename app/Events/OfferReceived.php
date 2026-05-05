<?php

namespace App\Events;

use App\Models\AuctionOffer;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OfferReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $offer;
    public $item;
    public $car;
    public $buyerName;
    public $amount;
    public $message;
    public $createdAt;
    public $sellerId;

    /**
     * Create a new event instance.
     */
    public function __construct(AuctionOffer $offer)
    {
        $this->offer = $offer;
        $this->item = $offer->auctionItem;
        $this->car = [
            'id' => $offer->auctionItem->car->id,
            'name' => $offer->auctionItem->car->name ?? '',
            'brand' => $offer->auctionItem->car->brand->name ?? '',
            'model' => $offer->auctionItem->car->model->name ?? '',
            'year' => $offer->auctionItem->car->year ?? '',
            'thumbnail' => $offer->auctionItem->car->thumbnail_img ?? '',
        ];
        // Anonymize buyer name
        $this->buyerName = 'Buyer #' . $offer->buyer_id;
        $this->amount = $offer->amount;
        $this->message = $offer->message;
        $this->createdAt = $offer->created_at;
        $this->sellerId = $offer->seller_id;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('seller.' . $this->sellerId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'OfferReceived';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'offer_id' => $this->offer->id,
            'item_id' => $this->item->id,
            'car' => $this->car,
            'buyer_name' => $this->buyerName,
            'amount' => $this->amount,
            'message' => $this->message,
            'created_at' => $this->createdAt,
        ];
    }
}
