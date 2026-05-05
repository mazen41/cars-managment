<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\Product;
use App\Models\Car;
use App\Models\CustomerProduct;

class WishlistCollection extends ResourceCollection
{
    private const TYPE_MAP = [
        Product::class => 'product',
        Car::class => 'car',
        CustomerProduct::class => 'customer_product',
    ];

    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                if (!$data->wishlistable) {
                    return null;
                }

                return [
                    'id' => (int) $data->id,
                    'type' => $this->getFriendlyType($data->wishlistable_type),
                    'item' => $this->formatItem($data->wishlistable)
                ];
            })->filter() // Remove null entries
        ];
    }

    private function getFriendlyType($modelClass)
    {
        return self::TYPE_MAP[$modelClass] ?? 'unknown';
    }

    private function formatItem($item)
    {
        $baseData = [
            'id' => $item->id,
            'slug' => $item->slug ?? null,
        ];

        if ($item instanceof Product) {
            return array_merge($baseData, [
                'name' => $item->name ?? translate('Item not found'),
                'thumbnail_image' => uploaded_asset($item->thumbnail_img),
                'base_price' => format_price(home_base_price($item, false)),
                'rating' => (double) ($item->rating ?? 0),
            ]);
        }

        if ($item instanceof Car) {
            return array_merge($baseData, [
                'name' => $item->car_name ?? translate('Item not found'),
                'thumbnail_image' => uploaded_asset($item->thumbnail_img ?? $item->main_image),
                'base_price' => format_price(convert_price($item->price ?? 0)),
                'rating' => (double) ($item->rating ?? 0),
            ]);
        }

        if ($item instanceof CustomerProduct) {
            return array_merge($baseData, [
                'name' => $item->name ?? translate('Item not found'),
                'thumbnail_image' => uploaded_asset($item->thumbnail_img),
                'base_price' => format_price(convert_price($item->unit_price ?? 0)),
                'rating' => (double) ($item->rating ?? 0),
            ]);
        }

        // Fallback for unknown types
        return array_merge($baseData, [
            'thumbnail_image' => null,
            'base_price' => format_price(0),
            'rating' => 0.0,
        ]);
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
