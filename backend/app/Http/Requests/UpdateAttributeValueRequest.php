<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAttributeValueRequest extends FormRequest
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
            'attribute_id' => 'required|exists:attributes,id',
            'value' => 'required|string|max:100|unique:attribute_values,value,'
                . $this->attributeValue->id . ',id,attribute_id,' . $this->attribute_id,

            'slug' => 'required|string|max:100|unique:attribute_values,slug,'
                . $this->attributeValue->id . ',id,attribute_id,' . $this->attribute_id,
            'status' => 'nullable|boolean',
        ];
    }

    public function messages()
    {
        return [
            'attribute_id.required' => 'Thuộc tính là bắt buộc',
            'attribute_id.exists' => 'Thuộc tính không tồn tại',
            'value.required' => 'Giá trị thuộc tính là bắt buộc',
            'value.max' => 'Giá trị thuộc tính không được vượt quá 100 ký tự',
            'value.unique' => 'Giá trị thuộc tính đã tồn tại trong thuộc tính này',
            'slug.required' => 'Slug là bắt buộc',
            'slug.max' => 'Slug không được vượt quá 100 ký tự',
            'slug.unique' => 'Slug đã tồn tại trong thuộc tính này',
            'status.boolean' => 'Trạng thái phải là true hoặc false',
        ];
    }
}
