<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\Review;
use App\Models\Attribute;


class ProductDetailCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($data) {
                $precision = 2;
                $calculable_price = home_discounted_base_price($data, false);
                $calculable_price = number_format($calculable_price, $precision, '.', '');
                $calculable_price = floatval($calculable_price);
                // $calculable_price = round($calculable_price, 2);
                $photo_paths = get_images_path($data->photos);

                $photos = [];


                if (!empty($photo_paths)) {
                    for ($i = 0; $i < count($photo_paths); $i++) {
                        if ($photo_paths[$i] != "") {
                            $item = array();
                            $item['variant'] = "";
                            $item['path'] = $photo_paths[$i];
                            $photos[] = $item;
                        }
                    }
                }

                foreach ($data->stocks as $stockItem) {
                    if ($stockItem->image != null && $stockItem->image != "") {
                        $item = array();
                        $item['variant'] = $stockItem->variant;
                        $item['path'] = uploaded_asset($stockItem->image);
                        $photos[] = $item;
                    }
                }

                $brand = [
                    'id' => 0,
                    'name' => "",
                    'slug' => "",
                    'logo' => "",
                ];

                if ($data->brand != null) {
                    $brand = [
                        'id' => $data->brand->id,
                        'slug' => $data->brand->slug,
                        'name' => $data->brand->getTranslation('name'),
                        'logo' => uploaded_asset($data->brand->logo),
                    ];
                }

                $whole_sale = [];
                if (addon_is_activated('wholesale')) {
                    $whole_sale =  ProductWholesaleResource::collection($data->stocks->first()->wholesalePrices);
                }
                return [
                    'id' => (int)$data->id,
                    'name' => $data->getTranslation('name'),
                    'slug' => $data->slug,
                    'added_by' => $data->added_by,
                    "main_category" => $data->main_category ?
                    [
                        'id' => $data->main_category->id,
                        'name' => $data->main_category->getTranslation('name'),
                        'icon' => uploaded_asset($data->main_category->icon),
                    ]
                    :  null,
                    "subcategory" => $data->subcategory ?
                    [
                        'id' => $data->subcategory->id,
                        'name' => $data->subcategory->getTranslation('name'),
                        'icon' => uploaded_asset($data->subcategory->icon),
                    ] : null,
                    'is_from_admin' => $data->added_by === 'admin',
                    'seller_id' => $data->user->id,
                    'shop_id' => $data->added_by == 'admin' ? 0 : $data->user->shop->id,
                    'shop_slug' => $data->added_by == 'admin' ? '' : $data->user->shop->slug,
                    'shop_name' => $data->added_by == 'admin' ? translate('In House Product') : $data->user->shop->name,
                    'shop_address'  => $data->added_by == 'admin' ? translate('Admin warehouse') : $data->user->shop->address,
                    'shop_logo' => $data->added_by == 'admin' ? uploaded_asset(get_setting('header_logo')) : uploaded_asset($data->user->shop->logo) ?? "",
                    "map_location" => $data->added_by == 'admin' ? [
                        "longtitude" => get_setting("google_map_longtitude"),
                        "latitude" => get_setting("google_map_latitude"),
                    ] :
                    [
                        "longitude" => $data->user->shop->delivery_pickup_longitude,
                        "latitude" => $data->user->shop->delivery_pickup_latitude
                    ],
                    'photos' => $photos,
                    'thumbnail_image' => uploaded_asset($data->thumbnail_img),
                    'tags' => explode(',', $data->tags),
                    'price_high_low' => (float)explode('-', home_discounted_base_price($data, false))[0] == (float)explode('-', home_discounted_price($data, false))[1] ? format_price((float)explode('-', home_discounted_price($data, false))[0]) : "From " . format_price((float)explode('-', home_discounted_price($data, false))[0]) . " to " . format_price((float)explode('-', home_discounted_price($data, false))[1]),
                    'choice_options' => $this->convertToChoiceOptions(json_decode($data->choice_options)),
                    'colors' => json_decode($data->colors) ?? [],
                    'has_discount' => home_base_price($data, false) != home_discounted_base_price($data, false),
                    'discount' => "-" . discount_in_percentage($data) . "%",
                    "discount_end_date" => $data->discount_end_date != null ? \Carbon\Carbon::createFromTimestamp($data->discount_end_date)->format('d-m-Y') : null,
                    'stroked_price' => home_base_price($data),
                    'main_price' => home_discounted_base_price($data),
                    'calculable_price' => $calculable_price,
                    'currency_symbol' => currency_symbol(),
                    'current_stock' => (int)$data->stocks->first()->qty,
                    'unit' => $data->unit ?? "",
                    'rating' => (float)$data->rating,
                    'rating_count' => (int)$data->rating_count,
                    'earn_point' => (float)$data->earn_point,
                    'description' => $data->getTranslation('description'),
                    'specifications' => $data->specifications ?? [],
                    'downloads' => $data->pdf ? uploaded_asset($data->pdf) : null,
                    'video_link' => $data->video_link != null ?  $data->video_link : "",
                    'brand' => $brand,
                    'link' => route('product', $data->slug),
                    'wholesale' => $whole_sale,
                    'est_shipping_time' => (int)$data->est_shipping_days,
                    'external_link' => $data->external_link != null ? $data->external_link : "",
                    'external_link_title' => $data->external_link_btn != null ? $data->external_link_btn : "",
                    'can_be_refunded' => (bool)$data->refundable,
                    'cash_on_delivery' => (bool)$data->cash_on_delivery,
                    'publish_date' => $data->created_at->format('d-m-Y H:i:s'),
                ];
            })
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }

    protected function convertToChoiceOptions($data)
    {
        $result = array();
        if ($data) {
            foreach ($data as $key => $choice) {
                $item['name'] = $choice->attribute_id;
                $item['title'] = Attribute::find($choice->attribute_id)->getTranslation('name');
                $item['options'] = $choice->values;
                array_push($result, $item);
            }
        }
        return $result;
    }

    protected function convertPhotos($data)
    {
        $result = array();
        foreach ($data as $key => $item) {
            array_push($result, uploaded_asset($item));
        }
        return $result;
    }
}
