<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'image' => $this->image,
            'sku' => $this->sku,
            'price' => (float) $this->price,
            'stock' => (int) $this->stock,
            'status' => $this->status,
            'product_variant_values_count' => $this->whenCounted('productVariantValues'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
