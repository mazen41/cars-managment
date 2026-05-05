<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->getTranslation('name'),
            'description' => $this->getTranslation('description'),
            'condition' => $this->condition,
            'price' => (float) $this->price,
            'formatted_price' => single_price($this->price),
            'main_photo_url' => $this->main_photo ? uploaded_asset($this->main_photo) : null,
            'photos' => $this->when(
                $request->routeIs('api.v2.customer-products.show') || 
                $request->routeIs('api.v2.public.customer-products.show'),
                function () {
                    $photoUrls = [];
                    if ($this->photos && is_array($this->photos)) {
                        foreach ($this->photos as $photoId) {
                            if ($photoId) {
                                $photoUrls[] = uploaded_asset($photoId);
                            }
                        }
                    }
                    return $photoUrls;
                }
            ),
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->getTranslation('name'),
                    'parent_id' => $this->category->parent_id,
                ];
            }),
            'location' => [
                'address' => $this->address,
                'state' => $this->whenLoaded('state', [
                    'id' => $this->state->id,
                    'name' => $this->state->name,
                ]),
                'city' => $this->whenLoaded('city', [
                    'id' => $this->city->id,
                    'name' => $this->city->getTranslation('name'),
                ]),
                'coordinates' => $this->when(
                    !is_null($this->latitude) && !is_null($this->longitude),
                    [
                        'latitude' => (float) $this->latitude,
                        'longitude' => (float) $this->longitude,
                    ]
                ),
            ],
            'status' => [
                'moderation' => $this->moderation_status,
                'availability' => $this->availability_status,
                'rejection_reason' => $this->when(
                    $this->moderation_status === 'rejected' && 
                    $this->rejection_reason &&
                    $this->isOwnedByCurrentUser(),
                    $this->rejection_reason
                ),
            ],
            'owner' => $this->when(
                $request->routeIs('api.v2.customer-products.show') || 
                $request->routeIs('api.v2.public.customer-products.show') ||
                $this->isOwnedByCurrentUser(),
                function () {
                    return $this->whenLoaded('user', [
                        'id' => $this->user->id,
                        'name' => $this->user->name,
                        'avatar_url' => $this->user->avatar ? uploaded_asset($this->user->avatar) : null,
                    ]);
                }
            ),
            'stats' => $this->when(
                $request->routeIs('api.v2.customer-products.show') || 
                $request->routeIs('api.v2.public.customer-products.show'),
                [
                    'views_count' => $this->views_count ?? 0,
                    'favorites_count' => $this->favorites_count ?? 0,
                    'inquiries_count' => $this->inquiries_count ?? 0,
                ]
            ),
            'permissions' => $this->when($this->isOwnedByCurrentUser(), [
                'can_edit' => $this->moderation_status !== 'pending',
                'can_delete' => true,
            ]),
            'admin_permissions' => $this->when(
                auth()->check() && 
                (auth()->user()->user_type === 'admin' || auth()->user()->user_type === 'staff'),
                [
                    'can_moderate' => true,
                    'can_approve' => $this->moderation_status === 'pending',
                    'can_reject' => $this->moderation_status === 'pending',
                    'can_delete' => true,
                ]
            ),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }

    /**
     * Check if the current user owns this product.
     */
    protected function isOwnedByCurrentUser(): bool
    {
        return auth()->check() && auth()->id() === $this->user_id;
    }
}