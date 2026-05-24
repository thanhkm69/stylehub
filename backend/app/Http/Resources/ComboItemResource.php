<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComboItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'combo_id'           => $this->combo_id,
            'product_id'         => $this->product_id,
            'product_variant_id' => $this->product_variant_id,
            'quantity'           => $this->quantity,
            'product'            => new ProductResource($this->whenLoaded('product')),
            'product_variant'    => new ProductVariantResource($this->whenLoaded('productVariant')),
            'created_at'         => $this->created_at?->toDateTimeString(),
            'updated_at'         => $this->updated_at?->toDateTimeString(),
        ];
    }
}
