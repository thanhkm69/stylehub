<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'sometimes|required|in:pending,confirmed,processing,shipping,delivered,cancelled,refunded',
            'payment_status' => 'sometimes|required|in:unpaid,paid,refunded',
            'note' => 'nullable|string',
            'cancelled_reason' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Trạng thái đơn hàng không hợp lệ.',
            'payment_status.in' => 'Trạng thái thanh toán không hợp lệ.',
        ];
    }
}
