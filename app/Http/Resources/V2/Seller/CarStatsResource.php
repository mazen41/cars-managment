<?php

namespace App\Http\Resources\V2\Seller;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarStatsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total_cars' => $this->resource['total_cars'],
            'published_cars' => $this->resource['published_cars'],
            'pending_cars' => $this->resource['pending_cars'],
            'rejected_cars' => $this->resource['rejected_cars'],
            'available_cars' => $this->resource['available_cars'],
            'reserved_cars' => $this->resource['reserved_cars'],
            'sold_cars' => $this->resource['sold_cars'],
            'in_auction_cars' => $this->resource['in_auction_cars'] ?? 0,
            'moderation_stats' => [
                'published' => [
                    'count' => $this->resource['published_cars'],
                    'percentage' => $this->resource['total_cars'] > 0 
                        ? round(($this->resource['published_cars'] / $this->resource['total_cars']) * 100, 1) 
                        : 0,
                ],
                'pending' => [
                    'count' => $this->resource['pending_cars'],
                    'percentage' => $this->resource['total_cars'] > 0 
                        ? round(($this->resource['pending_cars'] / $this->resource['total_cars']) * 100, 1) 
                        : 0,
                ],
                'rejected' => [
                    'count' => $this->resource['rejected_cars'],
                    'percentage' => $this->resource['total_cars'] > 0 
                        ? round(($this->resource['rejected_cars'] / $this->resource['total_cars']) * 100, 1) 
                        : 0,
                ],
            ],
            'status_stats' => [
                'available' => [
                    'count' => $this->resource['available_cars'],
                    'percentage' => $this->resource['total_cars'] > 0 
                        ? round(($this->resource['available_cars'] / $this->resource['total_cars']) * 100, 1) 
                        : 0,
                ],
                'reserved' => [
                    'count' => $this->resource['reserved_cars'],
                    'percentage' => $this->resource['total_cars'] > 0 
                        ? round(($this->resource['reserved_cars'] / $this->resource['total_cars']) * 100, 1) 
                        : 0,
                ],
                'sold' => [
                    'count' => $this->resource['sold_cars'],
                    'percentage' => $this->resource['total_cars'] > 0 
                        ? round(($this->resource['sold_cars'] / $this->resource['total_cars']) * 100, 1) 
                        : 0,
                ],
            ],
        ];
    }
}