<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantValueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_variant_id' => $this->product_variant_id,
            'attribute_value_id' => $this->attribute_value_id,
            'attribute_value' => $this->whenLoaded('attributeValue'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
