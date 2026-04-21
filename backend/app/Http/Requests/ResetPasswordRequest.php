<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => 'required|email|exists:users,email',
            'newPassword' => 'required|min:8|max:25|confirmed',
            'otp' => 'required|digits:6',
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'email.exists' => 'Địa chỉ email không tồn tại trong hệ thống.',
            'newPassword.required' => 'Vui lòng nhập mật khẩu mới.',
            'newPassword.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'newPassword.max' => 'Mật khẩu mới không được vượt quá 25 ký tự.',
            'newPassword.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
            'otp.required' => 'Vui lòng nhập mã OTP.',
            'otp.digits' => 'Mã OTP phải bao gồm 6 chữ số.',
        ];
    }
}
