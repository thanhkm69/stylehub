<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateComboRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => 'required|string|max:100',
            'description'    => 'nullable|string',
            'thumbnail'      => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'discount_type'  => 'required|in:percentage,fixed_price',
            'discount_value' => 'required|numeric|min:0',
            'starts_at'      => 'nullable|date',
            'ends_at'        => 'nullable|date|after:starts_at',
            'status'         => 'nullable|boolean',
            'display'        => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'           => 'Tên combo không được để trống.',
            'name.max'                => 'Tên combo tối đa 100 ký tự.',
            'thumbnail.image'         => 'Thumbnail phải là hình ảnh.',
            'thumbnail.mimes'         => 'Thumbnail chỉ chấp nhận định dạng: png, jpg, jpeg, webp.',
            'thumbnail.max'           => 'Thumbnail tối đa 2MB.',
            'discount_type.required'  => 'Loại giảm giá không được để trống.',
            'discount_type.in'        => 'Loại giảm giá không hợp lệ.',
            'discount_value.required' => 'Giá trị giảm không được để trống.',
            'discount_value.numeric'  => 'Giá trị giảm phải là số.',
            'discount_value.min'      => 'Giá trị giảm không được âm.',
            'ends_at.after'           => 'Ngày kết thúc phải sau ngày bắt đầu.',
            'display.integer'         => 'Thứ tự hiển thị phải là số nguyên.',
            'display.min'             => 'Thứ tự hiển thị không được nhỏ hơn 0.',
        ];
    }
}
