<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBannerRequest extends FormRequest
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
            'title' => 'nullable|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'link' => 'nullable|string|max:255',
            'status' => 'boolean',
            'position' => 'integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'Hình ảnh không được để trống.',
            'image.image' => 'Định dạng hình ảnh không hợp lệ.',
            'image.mimes' => 'Hình ảnh phải có định dạng jpeg, png, jpg, gif, webp.',
            'image.max' => 'Hình ảnh không được vượt quá 2MB.',
            'title.max' => 'Tiêu đề không được vượt quá 255 ký tự.',
            'link.max' => 'Đường dẫn không được vượt quá 255 ký tự.',
            'status.boolean' => 'Trạng thái không hợp lệ.',
            'position.integer' => 'Vị trí phải là số nguyên.',
            'position.min' => 'Vị trí phải lớn hơn hoặc bằng 0.',
        ];
    }
}
