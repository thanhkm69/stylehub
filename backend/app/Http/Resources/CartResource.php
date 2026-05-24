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
        $regularPrice = (float) ($this->product_variant_id ? $this->productVariant?->price : $this->product?->price);
        $pricing = $this->getAttribute('pricing') ?? [
            'price' => $regularPrice,
            'original_price' => $regularPrice,
            'flash_sale' => null,
        ];
        $price = (float) $pricing['price'];
        $subtotal = $price * $this->quantity;

        $variantName = '';
        if ($this->product_variant_id && $this->productVariant) {
            $variantName = $this->productVariant->productVariantValues->map(function ($pvv) {
                return $pvv->attributeValue?->value;
            })->filter()->implode(' / ');
        }

        return [
            'id' => $this->id,
            'quantity' => (int) $this->quantity,
            'price' => (float) $price,
            'original_price' => (float) $pricing['original_price'],
            'flash_sale' => $pricing['flash_sale'],
            'subtotal' => (float) $subtotal,
            'variant_name' => $variantName,
            'stock' => $this->product_variant_id ? (int) $this->productVariant?->stock : 0,
            'product' => new ProductListResource($this->whenLoaded('product')),
            'variant' => new ProductVariantPublicResource($this->whenLoaded('productVariant')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
