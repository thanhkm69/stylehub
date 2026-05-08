<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:255', 'unique:products,slug'],
            'price' => ['required', 'numeric', 'min:0'],
            'thumbnail' => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'status' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Vui lòng chọn danh mục.',
            'category_id.exists' => 'Danh mục không tồn tại.',
            'name.required' => 'Vui lòng nhập tên sản phẩm.',
            'name.max' => 'Tên sản phẩm không được vượt quá 100 ký tự.',
            'slug.required' => 'Vui lòng nhập đường dẫn tĩnh (slug).',
            'slug.unique' => 'Đường dẫn tĩnh (slug) đã tồn tại.',
            'price.required' => 'Vui lòng nhập giá sản phẩm.',
            'price.numeric' => 'Giá sản phẩm phải là số.',
            'price.min' => 'Giá sản phẩm không được nhỏ hơn 0.',
            'thumbnail.required' => 'Vui lòng tải lên ảnh đại diện.',
            'thumbnail.image' => 'Ảnh đại diện không đúng định dạng.',
            'thumbnail.mimes' => 'Ảnh đại diện phải có định dạng: png, jpg, jpeg, webp.',
            'thumbnail.max' => 'Ảnh đại diện không được vượt quá 2MB.',
            'status.boolean' => 'Trạng thái không hợp lệ.',
        ];
    }
}
