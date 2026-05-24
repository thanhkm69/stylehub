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
        $pricing = $this->getAttribute('pricing');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'price' => (float) $this->price,
            'sale_price' => $pricing && $pricing['flash_sale'] ? $pricing['price'] : null,
            'original_price' => $pricing && $pricing['flash_sale'] ? $pricing['original_price'] : null,
            'flash_sale' => $pricing['flash_sale'] ?? null,
            'sale_price_from_variant' => (bool) ($pricing['is_starting_price'] ?? false),
            'thumbnail' => $this->thumbnail,
            'status' => $this->status,
            'views' => $this->views,
            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
                'slug' => $this->category?->slug,
            ],
            'images' => ProductImagePublicResource::collection($this->whenLoaded('images')),
        ];
    }
}
