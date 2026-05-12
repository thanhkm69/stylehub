<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFlashSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string',
            'thumbnail'   => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'starts_at'   => 'required|date',
            'ends_at'     => 'required|date|after:starts_at',
            'status'      => 'nullable|in:draft,active,ended,cancelled',
            'display'     => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'        => 'Tên flash sale không được để trống.',
            'name.max'             => 'Tên flash sale tối đa 100 ký tự.',
            'thumbnail.image'      => 'Thumbnail phải là hình ảnh.',
            'thumbnail.mimes'      => 'Thumbnail chỉ chấp nhận định dạng: png, jpg, jpeg, webp.',
            'thumbnail.max'        => 'Thumbnail tối đa 2MB.',
            'starts_at.required'   => 'Ngày bắt đầu không được để trống.',
            'starts_at.date'       => 'Ngày bắt đầu không hợp lệ.',
            'ends_at.required'     => 'Ngày kết thúc không được để trống.',
            'ends_at.date'         => 'Ngày kết thúc không hợp lệ.',
            'ends_at.after'        => 'Ngày kết thúc phải sau ngày bắt đầu.',
            'status.in'            => 'Trạng thái không hợp lệ.',
            'display.integer'      => 'Thứ tự hiển thị phải là số nguyên.',
            'display.min'          => 'Thứ tự hiển thị không được nhỏ hơn 0.',
        ];
    }
}
