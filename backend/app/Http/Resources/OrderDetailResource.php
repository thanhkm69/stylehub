<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'variant_id' => $this->variant_id,
            'product_name' => $this->product_name,
            'variant_name' => $this->variant_name,
            'sku' => $this->sku,
            'price' => (float) $this->price,
            'quantity' => (int) $this->quantity,
            'subtotal' => (float) $this->subtotal,
            'product_thumbnail' => $this->product?->thumbnail,
            'product' => new ProductListResource($this->whenLoaded('product')),
            'variant' => new ProductVariantPublicResource($this->whenLoaded('productVariant')),
        ];
    }
}
