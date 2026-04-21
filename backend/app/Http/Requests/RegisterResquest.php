<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterResquest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|max:25',
            'password_confirm' => 'required|string|min:8|max:25|same:password',
            'otp' => 'required|digits:6',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Tên bắt buộc phải nhập',
            'name.string' => 'Tên phải là chuỗi ký tự',
            'name.max' => 'Tên không được vượt quá 255 ký tự',

            'email.required' => 'Email bắt buộc phải nhập',
            'email.email' => 'Email không đúng định dạng',
            'email.max' => 'Email không được vượt quá 255 ký tự',
            'email.unique' => 'Email này đã được sử dụng',

            'password.required' => 'Mật khẩu bắt buộc phải nhập',
            'password.string' => 'Mật khẩu phải là chuỗi ký tự',
            'password.min' => 'Mật khẩu phải ít nhất 8 ký tự',
            'password.max' => 'Mật khẩu không được vượt quá 25 ký tự',
            'password.same' => 'Mật khẩu không khớp',

            'password_confirm.required' => 'Xác nhận mật khẩu bắt buộc phải nhập',
            'password_confirm.string' => 'Xác nhận mật khẩu phải là chuỗi ký tự',
            'password_confirm.min' => 'Xác nhận mật khẩu phải ít nhất 8 ký tự',
            'password_confirm.max' => 'Xác nhận mật khẩu không được vượt quá 25 ký tự',
            'password_confirm.same' => 'Xác nhận mật khẩu không khớp',

            'otp.required' => 'OTP bắt buộc phải nhập',
            'otp.digits' => 'OTP phải là 6 chữ số',
        ];
    }
}
