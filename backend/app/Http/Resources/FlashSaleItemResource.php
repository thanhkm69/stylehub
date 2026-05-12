<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FlashSaleItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'flash_sale_id'      => $this->flash_sale_id,
            'product_id'         => $this->product_id,
            'product_variant_id' => $this->product_variant_id,
            'discount_type'      => $this->discount_type,
            'discount_value'     => $this->discount_value,
            'original_price'     => $this->original_price,
            'sale_price'         => $this->sale_price,
            'status'             => $this->status,
            'display'            => $this->display,
            'product'            => $this->whenLoaded('product', fn() => [
                'id'        => $this->product->id,
                'name'      => $this->product->name,
                'thumbnail' => $this->product->thumbnail,
                'price'     => $this->product->price,
            ]),
            'product_variant'    => $this->whenLoaded('productVariant', fn() => [
                'id'    => $this->productVariant?->id,
                'sku'   => $this->productVariant?->sku,
                'price' => $this->productVariant?->price,
            ]),
            'created_at'         => $this->created_at?->toDateTimeString(),
            'updated_at'         => $this->updated_at?->toDateTimeString(),
        ];
    }
}
