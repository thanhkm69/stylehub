<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'image' => 'required|image|mimes:png,jpg,jpeg,webp|max:2048',
            'sku' => 'required|string|max:100|unique:product_variants,sku',
            'price' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'status' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Sản phẩm là bắt buộc',
            'product_id.exists' => 'Sản phẩm không tồn tại',
            'image.required' => 'Hình ảnh là bắt buộc',
            'image.image' => 'File tải lên phải là hình ảnh',
            'image.mimes' => 'Hình ảnh phải có định dạng: png, jpg, jpeg, webp',
            'image.max' => 'Hình ảnh không được vượt quá 2MB',
            'sku.required' => 'Mã SKU là bắt buộc',
            'sku.string' => 'Mã SKU phải là chuỗi ký tự',
            'sku.max' => 'Mã SKU không được vượt quá 100 ký tự',
            'sku.unique' => 'Mã SKU đã tồn tại',
            'price.required' => 'Giá sản phẩm là bắt buộc',
            'price.numeric' => 'Giá sản phẩm phải là số',
            'price.min' => 'Giá sản phẩm không được nhỏ hơn 0',
            'stock.integer' => 'Số lượng tồn kho phải là số nguyên',
            'stock.min' => 'Số lượng tồn kho không được nhỏ hơn 0',
            'status.boolean' => 'Trạng thái phải là true hoặc false',
        ];
    }
}
