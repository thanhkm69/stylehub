<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'   => 'required|string|max:255',
            'email'  => 'required|email|unique:users,email',
            'role'   => 'nullable|in:Admin,user',
            'status' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'Tên người dùng không được để trống',
            'name.string'    => 'Tên người dùng phải là chuỗi ký tự',
            'name.max'       => 'Tên người dùng không được vượt quá 255 ký tự',
            'email.required' => 'Email không được để trống',
            'email.email'    => 'Email không đúng định dạng',
            'email.unique'   => 'Email đã tồn tại trong hệ thống',
            'role.in'        => 'Vai trò phải là Admin hoặc user',
            'status.boolean' => 'Trạng thái phải là boolean',
        ];
    }
}