<?php

namespace App\Http\Controllers\Api\V2;

use App\Enums\CarFuelTypeEnum;
use App\Enums\CarTransmissionTypeEnum;
use App\Enums\ConditionEnum;
use App\Http\Resources\V2\BrandCollection;
use App\Http\Resources\V2\CategoryCollection;
use App\Http\Resources\V2\AttributeCollection;
use App\Models\Brand;
use App\Models\Category;
use Cache;
use App\Models\Car;
use App\Models\CarFeature;
use App\Models\CarColor;
use App\Models\Attribute;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class FilterController extends Controller
{
    public function categories()
    {
        //if you want to show base categories
        return Cache::remember('app.filter_categories', 86400, function () {
            return new CategoryCollection(Category::where('parent_id', 0)->get());
        });

        //if you want to show featured categories
        //return new CategoryCollection(Category::where('featured', 1)->get());
    }

    public function brands()
    {
        //show only top 20 brands
        return Cache::remember('app.filter_brands', 86400, function () {
            return new BrandCollection(Brand::where('top', 1)->limit(20)->get());
        });
    }


    /**
     * Get available product filter options
     */
    public function generalMarketFilters(Request $request): JsonResponse
    {
        if($request->filled('category_id')){
            $category = Category::find($request->category_id);
            $attributes = $category ? $category->attributes()->with('attribute_values')->get() : [];

        } else {
            $attributes = Attribute::with('attribute_values')->paginate(20);
        }
         $attributes_collection = $attributes->map(function($attribute) {
                return [
                    'id'    => (int) $attribute->id,
                    'name'  => $attribute->name,
                    'values'=> $attribute->attribute_values->map(function($value) {
                        return [
                            'value' => $value->value,
                        ];
                    })
                ];
            });
        $filters = [
            "attributes" => $attributes_collection,
            "price_range" => [
                "min" => convert_price(DB::table("products")->where("approved", "1")->where('published', "1")->min("unit_price")),
                "max" => convert_price(DB::table("products")->where("approved", "1")->where('published', "1")->max("unit_price")),
            ],
        ];
        return response()->json([
            "result" => true,
            "data" => $filters,
            "message" => "Filter options retrieved successfully",
        ]);
    }

     /**
     * Get available customer product filter options
     */
    public function customerProductFilters(Request $request): JsonResponse
    {
        $filters = [
            "price_range" => [
                "min" => convert_price(DB::table("customer_products")->where("moderation_status", "1")->where('availability_status', "available")->min("price")),
                "max" => convert_price(DB::table("customer_products")->where("moderation_status", "1")->where('availability_status', "available")->max("price")),
            ],
        ];
        return response()->json([
            "result" => true,
            "data" => $filters,
            "message" => "Filter options retrieved successfully",
        ]);
    }


    /**
     * Get available car filter options
     */
    public function carFilters(Request $request): JsonResponse
    {
        $filters = [

            "features" => CarFeature::orderBy("name")
                ->get(["id", "name"]),

            "colors" => CarColor::orderBy("name")
                ->get(["id", "name", "hex_code"]),

            "conditions" => ConditionEnum::options(),

            "transmissions" => CarTransmissionTypeEnum::options(),

            "fuel_types" => CarFuelTypeEnum::options(),

            "price_range" => [
                "min" => convert_price(Car::published()->min("price")),
                "max" => convert_price(Car::published()->max("price")),
            ],

            "year_range" => [
                "min" => Car::published()->min("manufacture_year"),
                "max" => Car::published()->max("manufacture_year"),
            ],

            "milage_range" => [
                "min" => Car::published()->min("milage"),
                "max" => Car::published()->max("milage"),
            ],
        ];

        return response()->json([
            "result" => true,
            "data" => $filters,
            "message" => "Filter options retrieved successfully",
        ]);
    }



}
