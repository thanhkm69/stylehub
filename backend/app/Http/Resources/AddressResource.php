<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'province_id' => $this->province_id,
            'province_name' => $this->province_name,
            'district_id' => $this->district_id,
            'district_name' => $this->district_name,
            'ward_code' => $this->ward_code,
            'ward_name' => $this->ward_name,
            'address' => $this->address,
            'is_default' => $this->is_default,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
