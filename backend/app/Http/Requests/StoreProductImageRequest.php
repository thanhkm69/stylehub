<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductImageRequest extends FormRequest
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
        return [
            'product_id' => 'required|exists:products,id',
            'image' => 'required|image|mimes:png,jpg,jpeg,webp|max:2048',
            'alt' => 'required|string|max:255',
            'display' => 'nullable|integer|min:0',
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
            'alt.required' => 'Thẻ alt là bắt buộc',
            'alt.string' => 'Thẻ alt phải là chuỗi',
            'alt.max' => 'Thẻ alt không được vượt quá 255 ký tự',
            'display.integer' => 'Thứ tự hiển thị phải là số nguyên',
            'display.min' => 'Thứ tự hiển thị không được nhỏ hơn 0',
            'status.boolean' => 'Trạng thái phải là true hoặc false',
        ];
    }
}
