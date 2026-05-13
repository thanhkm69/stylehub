<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array (full detail for product page).
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'price'       => (float) $this->price,
            'description' => $this->description,
            'thumbnail'   => $this->thumbnail,
            'status'      => $this->status,
            'views'       => $this->views,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
            'category'    => [
                'id'    => $this->category?->id,
                'name'  => $this->category?->name,
                'slug'  => $this->category?->slug,
                'image' => $this->category?->image,
            ],
            'images'   => ProductImagePublicResource::collection($this->whenLoaded('images')),
            'variants' => ProductVariantPublicResource::collection($this->whenLoaded('variants')),
            'similar_products' => ProductListResource::collection($this->whenLoaded('similarProducts')),
            'outfit_products' => ProductListResource::collection($this->whenLoaded('outfitProducts')),
        ];
    }
}
