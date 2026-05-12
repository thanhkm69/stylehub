<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComboResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'description'    => $this->description,
            'thumbnail'      => $this->thumbnail,
            'combo_type'     => $this->combo_type,
            'discount_type'  => $this->discount_type,
            'discount_value' => $this->discount_value,
            'starts_at'      => $this->starts_at?->toDateTimeString(),
            'ends_at'        => $this->ends_at?->toDateTimeString(),
            'status'         => $this->status,
            'display'        => $this->display,
            'created_at'     => $this->created_at?->toDateTimeString(),
            'updated_at'     => $this->updated_at?->toDateTimeString(),
        ];
    }
}
