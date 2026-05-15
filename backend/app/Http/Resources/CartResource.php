<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $price = $this->product_variant_id ? $this->productVariant?->price : $this->product?->price;
        $subtotal = $price * $this->quantity;

        $variantName = '';
        if ($this->product_variant_id && $this->productVariant) {
            $variantName = $this->productVariant->productVariantValues->map(function($pvv) {
                return $pvv->attributeValue?->value;
            })->filter()->implode(' / ');
        }

        return [
            'id' => $this->id,
            'quantity' => (int) $this->quantity,
            'price' => (float) $price,
            'subtotal' => (float) $subtotal,
            'variant_name' => $variantName,
            'stock' => $this->product_variant_id ? (int) $this->productVariant?->stock : 0,
            'product' => new ProductListResource($this->whenLoaded('product')),
            'variant' => new ProductVariantPublicResource($this->whenLoaded('productVariant')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
