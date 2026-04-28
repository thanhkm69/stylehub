<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
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
        // Category id passed as parameter in the URL. Since $this->route('category') might be just ID if route is bound implicitly, or the model if explicitly bound.
        // Let's check CategoryController.php: it receives $id. So we use $this->route('id') or $this->route('category')?
        // In CategoryController.php: public function update(Request $request, $id) -> so it receives 'id' in route.
        // The parameter is $this->route('id') or similar depending on the route definition.
        // Wait, route:list showed api/categories/{id}
        $categoryId = $this->route('id') ?? $this->route('category');

        return [
            'parent_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:150|unique:categories,slug,' . $categoryId,
            'image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'description' => 'nullable|string',
            'display' => 'nullable|integer|min:0',
            'status' => 'nullable|boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'parent_id.exists' => 'Danh mục cha không hợp lệ',
            'name.required' => 'Tên danh mục không được để trống',
            'name.max' => 'Tên danh mục không vượt quá 100 ký tự',
            'slug.required' => 'Slug danh mục không được để trống',
            'slug.max' => 'Slug danh mục không vượt quá 150 ký tự',
            'slug.unique' => 'Slug danh mục đã tồn tại',
            'image.image' => 'Ảnh danh mục không hợp lệ',
            'image.mimes' => 'Định dạng ảnh phải là png, jpg, jpeg, hoặc webp',
            'image.max' => 'Dung lượng ảnh không vượt quá 2048 KB',
            'display.integer' => 'Vị trí hiển thị phải là số nguyên',
            'display.min' => 'Vị trí hiển thị không được nhỏ hơn 0',
            'status.boolean' => 'Trạng thái danh mục phải là boolean',
        ];
    }
}
