<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAttributeRequest extends FormRequest
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
            'name' => 'required|string|max:100|unique:attributes,name',
            'slug' => 'required|string|max:100|unique:attributes,slug',
            'status' => 'boolean|nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên thuộc tính không được để trống',
            'name.unique' => 'Tên thuộc tính đã tồn tại',
            'slug.required' => 'Slug thuộc tính không được để trống',
            'slug.unique' => 'Slug thuộc tính đã tồn tại',
            'status.boolean' => 'Trạng thái thuộc tính phải là boolean',
        ];
    }
}
