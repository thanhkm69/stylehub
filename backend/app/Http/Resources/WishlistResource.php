<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WishlistResource extends JsonResource
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
            'product' => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'slug' => $this->product->slug,
                'price' => (float) $this->product->price,
                'thumbnail' => $this->product->thumbnail,
                'category' => [
                    'id' => $this->product->category?->id,
                    'name' => $this->product->category?->name,
                    'slug' => $this->product->category?->slug,
                ],
                'images' => ProductImagePublicResource::collection($this->product->images ?? []),
            ],
            'created_at' => $this->created_at,
        ];
    }
}
