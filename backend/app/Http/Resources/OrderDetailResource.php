<?php

namespace App\Http\Resources;

use App\Models\Review;
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
            'is_reviewed' => Review::where('user_id', auth()->id() ?? $this->order?->user_id)
                ->where('order_id', $this->order_id)
                ->where('product_id', $this->product_id)
                ->exists(),
            'review_id' => Review::where('user_id', auth()->id() ?? $this->order?->user_id)
                ->where('order_id', $this->order_id)
                ->where('product_id', $this->product_id)
                ->value('id'),
            'product' => new ProductListResource($this->whenLoaded('product')),
            'variant' => new ProductVariantPublicResource($this->whenLoaded('productVariant')),
        ];
    }
}
