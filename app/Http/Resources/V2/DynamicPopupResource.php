<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DynamicPopupResource extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
           'data' => $this->collection->map(function ($data){
            return [
                'id'    => $data->id,
                'title' => $data->title,
                'summary' => $data->summary,
                'banner' => uploaded_asset($data->banner),
                'link'  => $data->btn_link,
                'btn_text' => $data->btn_text,
                'dark_text_color' => $data->btn_text_color == "dark",
                "button_color" => $data->btn_background_color
            ];
           })
        ];
    }

     public function with($request)
    {
        return [
            "success" => true,
            "status" => 200,
        ];
    }
}
