<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFlashSaleItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'flash_sale_id'      => 'required|exists:flash_sales,id',
            'product_id'         => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'discount_type'      => 'required|in:percentage,fixed_price',
            'discount_value'     => 'required|numeric|min:1',
            'status'             => 'nullable|boolean',
            'display'            => 'nullable|integer|min:0',
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
            } elseif ($type === 'fixed_price') {
                if ($this->product_variant_id) {
                    $variant   = ProductVariant::find($this->product_variant_id);
                    $maxPrice  = (float) ($variant?->price ?? 0);
                } else {
                    $product   = Product::find($this->product_id);
                    $maxPrice  = (float) ($product?->price ?? 0);
                }

                if ($maxPrice > 0 && $value > $maxPrice) {
                    $validator->errors()->add(
                        'discount_value',
                        'Giá giảm cố định không được vượt quá giá sản phẩm (' . number_format($maxPrice, 0, ',', '.') . 'đ).'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'flash_sale_id.required'      => 'Flash Sale không được để trống.',
            'flash_sale_id.exists'        => 'Flash Sale không tồn tại.',
            'product_id.required'         => 'Sản phẩm không được để trống.',
            'product_id.exists'           => 'Sản phẩm không tồn tại.',
            'product_variant_id.exists'   => 'Biến thể sản phẩm không tồn tại.',
            'discount_type.required'      => 'Loại giảm giá không được để trống.',
            'discount_type.in'            => 'Loại giảm giá không hợp lệ (percentage hoặc fixed_price).',
            'discount_value.required'     => 'Giá trị giảm không được để trống.',
            'discount_value.numeric'      => 'Giá trị giảm phải là số.',
            'discount_value.min'          => 'Giá trị giảm không được âm.',
            'display.integer'             => 'Thứ tự hiển thị phải là số nguyên.',
            'display.min'                 => 'Thứ tự hiển thị không được nhỏ hơn 0.',
        ];
    }
}
