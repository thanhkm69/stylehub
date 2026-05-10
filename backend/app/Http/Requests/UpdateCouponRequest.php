<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCouponRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $couponId = $this->route('coupon')->id ?? $this->route('coupon');

        return [
            'code' => 'required|string|max:50|unique:coupons,code,' . $couponId,
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:1',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'min_order_value' => 'required|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'required|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'status' => 'boolean',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $type  = $this->discount_type;
            $value = (float) $this->discount_value;

            if ($type === 'percentage') {
                if ($value < 1 || $value > 100) {
                    $validator->errors()->add(
                        'discount_value',
                        'Phần trăm giảm phải từ 1 đến 100.'
                    );
                }
            } elseif ($type === 'fixed') {
                if ($value < 1) {
                    $validator->errors()->add(
                        'discount_value',
                        'Giá trị giảm cố định phải ít nhất 1đ.'
                    );
                }
            }
        });
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Mã giảm giá không được để trống.',
            'code.unique' => 'Mã giảm giá đã tồn tại.',
            'code.max' => 'Mã giảm giá tối đa 50 ký tự.',
            'name.required' => 'Tên mã giảm giá không được để trống.',
            'discount_type.required' => 'Loại giảm giá không được để trống.',
            'discount_type.in' => 'Loại giảm giá không hợp lệ.',
            'discount_value.required' => 'Giá trị giảm giá không được để trống.',
            'discount_value.numeric' => 'Giá trị giảm giá phải là số.',
            'min_order_value.required' => 'Giá trị đơn hàng tối thiểu không được để trống.',
            'expires_at.after_or_equal' => 'Ngày hết hạn phải sau hoặc bằng ngày bắt đầu.',
        ];
    }
}
