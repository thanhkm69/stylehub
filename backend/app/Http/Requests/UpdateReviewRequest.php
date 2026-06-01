<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateReviewRequest extends FormRequest
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
            'product_id' => ['sometimes', 'required', 'exists:products,id'],
            'order_id' => ['sometimes', 'required', 'exists:orders,id'],
            'rating' => ['sometimes', 'required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['sometimes', 'integer', 'in:0,1,2'],
        ];
    }

    /**
     * Custom Vietnamese messages for validation.
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'Mã sản phẩm không được để trống.',
            'product_id.exists' => 'Sản phẩm không tồn tại.',
            'order_id.required' => 'Mã đơn hàng không được để trống.',
            'order_id.exists' => 'Đơn hàng không tồn tại.',
            'rating.required' => 'Điểm đánh giá không được để trống.',
            'rating.integer' => 'Điểm đánh giá phải là số nguyên.',
            'rating.min' => 'Điểm đánh giá tối thiểu là 1.',
            'rating.max' => 'Điểm đánh giá tối đa là 5.',
            'comment.string' => 'Nội dung đánh giá phải là chuỗi.',
            'comment.max' => 'Nội dung đánh giá không được vượt quá 1000 ký tự.',
            'images.array' => 'Hình ảnh phải là một danh sách.',
            'images.max' => 'Bạn chỉ được tải lên tối đa 5 hình ảnh.',
            'images.*.image' => 'Tệp tải lên phải là hình ảnh.',
            'images.*.mimes' => 'Hình ảnh phải có định dạng: jpg, jpeg, png, webp.',
            'images.*.max' => 'Dung lượng mỗi hình ảnh không được vượt quá 2MB.',
        ];
    }

    /**
     * Override failedValidation to standard format
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $validator->errors()->first(),
            'data' => null,
        ], 422));
    }
}
