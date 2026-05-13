<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantPublicResource extends JsonResource
{
    /**
     * Transform the resource into an array (public variant with attribute values).
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'     => $this->id,
            'sku'    => $this->sku,
            'price'  => (float) $this->price,
            'stock'  => (int) $this->stock,
            'image'  => $this->image,
            'status' => $this->status,
            'attribute_values' => $this->whenLoaded('productVariantValues', function () {
                return $this->productVariantValues->map(function ($pvv) {
                    return [
                        'value'     => $pvv->attributeValue?->value,
                        'slug'      => $pvv->attributeValue?->slug,
                        'attribute' => [
                            'id'   => $pvv->attributeValue?->attribute?->id,
                            'name' => $pvv->attributeValue?->attribute?->name,
                            'slug' => $pvv->attributeValue?->attribute?->slug,
                        ],
                    ];
                });
            }),
        ];
    }
}
