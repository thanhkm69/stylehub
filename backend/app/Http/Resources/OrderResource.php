<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $info = $this->shipping_info ?? [];

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'coupon_id' => $this->coupon_id,
            'order_code' => $this->order_code,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'subtotal_amount' => (float) $this->subtotal_amount,
            'discount_amount' => (float) $this->discount_amount,
            'shipping_fee' => (float) $this->shipping_fee,
            'total_amount' => (float) $this->total_amount,
            
            // Ánh xạ ngược từ JSON ra các trường phẳng để Frontend không bị lỗi
            'shipping_name' => $info['name'] ?? null,
            'shipping_phone' => $info['phone'] ?? null,
            'shipping_email' => $info['email'] ?? null,
            'shipping_address' => $info['address'] ?? null,
            'shipping_province' => $info['province'] ?? null,
            'shipping_district' => $info['district'] ?? null,
            'shipping_ward' => $info['ward'] ?? null,
            'shipping_full_address' => $this->shipping_full_address,
            
            'note' => $this->note,
            'confirmed_at' => $this->confirmed_at?->format('Y-m-d H:i:s'),
            'shipped_at' => $this->shipped_at?->format('Y-m-d H:i:s'),
            'delivered_at' => $this->delivered_at?->format('Y-m-d H:i:s'),
            'cancelled_at' => $this->cancelled_at?->format('Y-m-d H:i:s'),
            'cancelled_reason' => $this->cancelled_reason,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'user' => $this->whenLoaded('user'),
            'coupon' => $this->whenLoaded('coupon'),
            'order_details' => OrderDetailResource::collection($this->whenLoaded('orderDetails')),
        ];
    }
}
