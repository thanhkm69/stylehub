<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductListResource extends JsonResource
{
    /**
     * Transform the resource into an array (compact version for listings).
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'slug'      => $this->slug,
            'price'     => (float) $this->price,
            'thumbnail' => $this->thumbnail,
            'status'    => $this->status,
            'views'     => $this->views,
            'category'  => [
                'id'   => $this->category?->id,
                'name' => $this->category?->name,
                'slug' => $this->category?->slug,
            ],
            'images' => ProductImagePublicResource::collection($this->whenLoaded('images')),
        ];
    }
}
