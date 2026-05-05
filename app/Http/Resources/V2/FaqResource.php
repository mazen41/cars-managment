<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FaqResource extends JsonResource
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
            'slug' => $this->slug,
            'type'  => $this->type,
            'question' => $this->getTranslation('question'),
            'answer' => $this->getTranslation('answer'),
            'sort_order' => $this->sort_order,
        ];
    }
}
