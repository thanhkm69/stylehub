<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'coupon_id' => 'nullable|exists:coupons,id',
            'payment_method' => 'required|in:cod,bank_transfer,momo,vnpay',
            'shipping_name' => 'required|string|max:100',
            'shipping_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string',
            'shipping_province' => 'nullable|string|max:100',
            'shipping_district' => 'nullable|string|max:100',
            'shipping_ward' => 'nullable|string|max:100',
            'note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'ID người dùng là bắt buộc.',
            'user_id.exists' => 'Người dùng không tồn tại.',
            'shipping_name.required' => 'Tên người nhận không được để trống.',
            'shipping_phone.required' => 'Số điện thoại người nhận không được để trống.',
            'shipping_address.required' => 'Địa chỉ giao hàng không được để trống.',
            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán.',
            'payment_method.in' => 'Phương thức thanh toán không hợp lệ.',
            'items.required' => 'Đơn hàng phải có ít nhất một sản phẩm.',
            'items.array' => 'Dữ liệu sản phẩm không đúng định dạng.',
            'items.*.product_id.required' => 'ID sản phẩm không được để trống.',
            'items.*.product_id.exists' => 'Sản phẩm không tồn tại.',
            'items.*.quantity.min' => 'Số lượng sản phẩm phải lớn hơn 0.',
        ];
    }
}
