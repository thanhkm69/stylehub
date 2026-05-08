<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductVariantValueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_variant_id' => 'required|exists:product_variants,id',
            'attribute_value_id' => 'required|exists:attribute_values,id',
        ];
    }

    public function messages(): array
    {
        return [
            'product_variant_id.required' => 'Biến thể sản phẩm là bắt buộc',
            'product_variant_id.exists' => 'Biến thể sản phẩm không tồn tại',
            'attribute_value_id.required' => 'Giá trị thuộc tính là bắt buộc',
            'attribute_value_id.exists' => 'Giá trị thuộc tính không tồn tại',
        ];
    }
}
