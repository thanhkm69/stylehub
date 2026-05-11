<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateComboItemRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'combo_id'           => 'required|exists:combos,id',
            'product_id'         => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'role'               => 'required|in:main,gift,bundle',
            'quantity'           => 'required|integer|min:1',
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'combo_id.required'      => 'Combo không được để trống.',
            'combo_id.exists'        => 'Combo không tồn tại.',
            'product_id.required'    => 'Sản phẩm không được để trống.',
            'product_id.exists'      => 'Sản phẩm không tồn tại.',
            'product_variant_id.exists' => 'Biến thể không tồn tại.',
            'role.required'          => 'Vai trò không được để trống.',
            'role.in'                => 'Vai trò không hợp lệ (main, gift, bundle).',
            'quantity.required'      => 'Số lượng không được để trống.',
            'quantity.integer'       => 'Số lượng phải là số nguyên.',
            'quantity.min'           => 'Số lượng tối thiểu là 1.',
        ];
    }
}
