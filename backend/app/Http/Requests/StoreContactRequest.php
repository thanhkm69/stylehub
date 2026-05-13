<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
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
            'full_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'regex:/^(\+84|0)(3|5|7|8|9)[0-9]{8}$/'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Vui lòng nhập họ và tên.',
            'full_name.string' => 'Họ và tên phải là chuỗi ký tự.',
            'full_name.max' => 'Họ và tên không được vượt quá 100 ký tự.',
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không hợp lệ.',
            'email.max' => 'Địa chỉ email không được vượt quá 150 ký tự.',
            'phone.regex' => 'Số điện thoại không hợp lệ. Ví dụ: 0123456789 hoặc +84123456789.',
            'subject.required' => 'Vui lòng nhập chủ đề.',
            'subject.string' => 'Chủ đề phải là chuỗi ký tự.',
            'subject.max' => 'Chủ đề không được vượt quá 150 ký tự.',
            'message.required' => 'Vui lòng nhập nội dung tin nhắn.',
            'message.string' => 'Nội dung tin nhắn phải là chuỗi ký tự.',
            'message.min' => 'Nội dung tin nhắn phải có ít nhất 10 ký tự.',
        ];
    }
}
