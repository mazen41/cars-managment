<?php

namespace App\Http\Resources\V2\Seller;

use App\Enums\CarFuelTypeEnum;
use App\Enums\CarTransmissionTypeEnum;
use App\Enums\ConditionEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarFormDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'brands' => $this->resource['brands']->map(function ($brand) {
                return [
                    'id' => $brand->id,
                    'name' => $brand->getTranslation('name'),
                    'logo_url' => $brand->logo_url,
                ];
            }),
            'categories' => $this->resource['categories']->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->getTranslation('name'),
                    'image_url' => $category->image_url,
                    'parent_id' => $category->parent_id,
                ];
            }),
            'feature_sections' => $this->resource['features']
                ->groupBy('section_id')
                ->map(function ($features, $sectionId) {
                    $section = $features->first()->section;

                    return [
                        'section_name' => $section ? $section->name : 'General',
                        'features' => $features->map(function ($feature) {
                            return [
                                'id' => $feature->id,
                                'name' => $feature->name,
                                'image_url' => $feature->image_url,
                            ];
                        })->values(),
                    ];
                })->values(),
            'custom_fields' => $this->resource['custom_fields']->map(function ($field) {
                return [
                    'id' => $field->id,
                    'name' => $field->name,
                    'type' => $field->type,
                    'required' => $field->required,
                    'placeholder' => $field->placeholder,
                    'options' => $field->options ? $field->options->map(function ($option) {
                        return [
                            'id' => $option->id,
                            'value' => $option->value,
                            'label' => $option->label,
                        ];
                    }) : [],
                ];
            }),
            'countries' => $this->resource['countries']->map(function ($country) {
                return [
                    'id' => $country->id,
                    'name' => $country->name,
                    'code' => $country->code,
                ];
            }),
            'colors' => $this->resource['colors']->map(function ($color) {
                return [
                    'id' => $color->id,
                    'name' => $color->getTranslation('name'),
                    'hex_code' => $color->hex_code,
                ];
            }),
            'conditions' => ConditionEnum::options(),
            'transmissions' => CarTransmissionTypeEnum::options(),
            'fuel_types' =>  CarFuelTypeEnum::options(),
        ];
    }

    public function with(Request $request)
    {
        return [
            'success' => true,
            'message'   => 'Car Form Retrieved Successfully',
            'status' => 200
        ];
    }
}
