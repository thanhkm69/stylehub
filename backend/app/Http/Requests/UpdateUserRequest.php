<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('user')?->id;

        return [
            'name'     => 'sometimes|string|max:255',
            'email'    => 'sometimes|email|unique:users,email,' . $userId,
            'password' => 'nullable|string|min:8',
            'role'     => 'sometimes|in:Admin,user',
            'status'   => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string'    => 'Tên người dùng phải là chuỗi ký tự',
            'name.max'       => 'Tên người dùng không được vượt quá 255 ký tự',
            'email.email'    => 'Email không đúng định dạng',
            'email.unique'   => 'Email đã tồn tại trong hệ thống',
            'password.min'   => 'Mật khẩu phải có ít nhất 8 ký tự',
            'role.in'        => 'Vai trò phải là Admin hoặc user',
            'status.boolean' => 'Trạng thái phải là boolean',
        ];
    }
}