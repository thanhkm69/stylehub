<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^(03|05|07|08|09|01[2|6|8|9])+([0-9]{8})$/'],
            'province_id' => ['required', 'integer'],
            'province_name' => ['required', 'string', 'max:100'],
            'district_id' => ['required', 'integer'],
            'district_name' => ['required', 'string', 'max:100'],
            'ward_code' => ['required', 'string', 'max:20'],
            'ward_name' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Họ tên không được để trống',
            'phone.required' => 'Số điện thoại không được để trống',
            'phone.regex' => 'Số điện thoại không đúng định dạng Việt Nam',
            'province_id.required' => 'Vui lòng chọn Tỉnh/Thành phố',
            'district_id.required' => 'Vui lòng chọn Quận/Huyện',
            'ward_code.required' => 'Vui lòng chọn Phường/Xã',
            'address.required' => 'Địa chỉ chi tiết không được để trống',
        ];
    }
}
