<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSelfProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $profileId = $this->user()?->profile?->id;

        return [
            'full_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('profiles', 'email')->ignore($profileId)],
            'phone' => ['nullable', 'string', 'max:20'],
            'gender' => ['nullable', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date'],
            'hobbies' => ['nullable', 'string'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Vui lòng nhập họ tên.',
            'full_name.string' => 'Họ tên phải là chuỗi ký tự.',
            'full_name.max' => 'Họ tên không được vượt quá 100 ký tự.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.max' => 'Email không được vượt quá 150 ký tự.',
            'email.unique' => 'Email đã được sử dụng.',
            'phone.string' => 'Số điện thoại phải là chuỗi ký tự.',
            'phone.max' => 'Số điện thoại không được vượt quá 20 ký tự.',
            'gender.in' => 'Giới tính không hợp lệ.',
            'date_of_birth.date' => 'Ngày sinh không đúng định dạng.',
            'hobbies.string' => 'Sở thích phải là chuỗi ký tự.',
            'occupation.string' => 'Nghề nghiệp phải là chuỗi ký tự.',
            'occupation.max' => 'Nghề nghiệp không được vượt quá 100 ký tự.',
            'status.boolean' => 'Trạng thái không hợp lệ.',
        ];
    }
}
