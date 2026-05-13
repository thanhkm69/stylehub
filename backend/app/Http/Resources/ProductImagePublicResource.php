<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductImagePublicResource extends JsonResource
{
    /**
     * Transform the resource into an array (public-facing, no internal fields).
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'      => $this->id,
            'image'   => $this->image,
            'alt'     => $this->alt,
            'display' => $this->display,
        ];
    }
}
