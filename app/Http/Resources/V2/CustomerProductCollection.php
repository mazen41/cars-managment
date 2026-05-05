<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CustomerProductCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($product) use ($request) {
                return [
                    'id' => $product->id,
                    'name' => $product->getTranslation('name'),
                    'description' => $this->truncateDescription($product->getTranslation('description')),
                    'condition' => $product->condition,
                    'price' => (float) $product->price,
                    'formatted_price' => single_price($product->price),
                    'main_photo_url' => $product->main_photo ? uploaded_asset($product->main_photo) : null,
                    'photos' => $this->getPhotosUrls($product->photos),
                    'category' => $product->category ? [
                        'id' => $product->category->id,
                        'name' => $product->category->getTranslation('name'),
                        'parent_id' => $product->category->parent_id,
                    ] : null,
                    'location' => [
                        'address' => $this->truncateAddress($product->address),
                        'state' => $product->state ? [
                            'id' => $product->state->id,
                            'name' => $product->state->name,
                        ] : null,
                        'city' => $product->city ? [
                            'id' => $product->city->id,
                            'name' => $product->city->getTranslation('name'),
                        ] : null,
                    ],
                    'status' => [
                        'moderation' => $product->moderation_status,
                        'availability' => $product->availability_status,
                    ],
                    'owner' => [
                        'id' => $product->user->id,
                        'name' => $product->user->name,
                        'phone'=> $product->user->phone,
                        'avatar_url' => $product->user->avatar ? uploaded_asset($product->user->avatar) : null,
                    ],
                    'stats' => [
                        'views_count' => $product->views_count ?? 0,
                        'favorites_count' => $product->favorites_count ?? 0,
                    ],
                    'permissions' => $this->getPermissions($product),
                    'created_at' => $product->created_at->toISOString(),
                    'updated_at' => $product->updated_at->toISOString(),
                    'links' => [
                        'details' => route('api.v2.customer-products.show', $product->id),
                        'public' => route('api.v2.public.customer-products.show', $product->id),
                    ],
                ];
            }),
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200,
        ];
    }

    /**
     * Truncate description for list view.
     */
    protected function truncateDescription(string $description, int $limit = 150): string
    {
        if (strlen($description) <= $limit) {
            return $description;
        }

        return substr($description, 0, $limit) . '...';
    }

    /**
     * Truncate address for list view.
     */
    protected function truncateAddress(string $address, int $limit = 50): string
    {
        if (strlen($address) <= $limit) {
            return $address;
        }

        return substr($address, 0, $limit) . '...';
    }


    /**
     * Get permissions for the current user.
     */
    protected function getPermissions($product): array
    {
        if (!auth()->check()) {
            return [];
        }

        $permissions = [];

        // Owner permissions
        if (auth()->id() === $product->user_id) {
            $permissions['can_edit'] = $product->moderation_status !== 'pending';
            $permissions['can_delete'] = true;
        }

        return $permissions;
    }

    /**
     * Get applied filters from request.
     */
    protected function getAppliedFilters($request): array
    {
        $filters = [];

        if ($request->has('category_id')) {
            $filters['category_id'] = $request->input('category_id');
        }

        if ($request->has('state_id')) {
            $filters['state_id'] = $request->input('state_id');
        }

        if ($request->has('city_id')) {
            $filters['city_id'] = $request->input('city_id');
        }

        if ($request->has('condition')) {
            $filters['condition'] = $request->input('condition');
        }

        if ($request->has('price_min')) {
            $filters['price_min'] = $request->input('price_min');
        }

        if ($request->has('price_max')) {
            $filters['price_max'] = $request->input('price_max');
        }

        if ($request->has('moderation_status')) {
            $filters['moderation_status'] = $request->input('moderation_status');
        }

        if ($request->has('availability_status')) {
            $filters['availability_status'] = $request->input('availability_status');
        }

        if ($request->has('search')) {
            $filters['search'] = $request->input('search');
        }

        return $filters;
    }

    /**
     * Get applied sorting from request.
     */
    protected function getAppliedSorting($request): array
    {
        return [
            'sort_by' => $request->input('sort_by', 'created_at'),
            'sort_order' => $request->input('sort_order', 'desc'),
        ];
    }

    protected function getPhotosUrls($photos) {
        $photoUrls = [];
        if ($photos && is_array($photos)) {
            foreach ($photos as $photoId) {
                if ($photoId) {
                    $photoUrls[] = uploaded_asset($photoId);
                }
            }
        }
        return $photoUrls;
    }
}
