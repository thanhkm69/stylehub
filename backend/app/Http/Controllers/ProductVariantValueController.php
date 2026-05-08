<?php

namespace App\Http\Controllers;

use App\Models\ProductVariantValue;
use App\Http\Requests\StoreProductVariantValueRequest;
use App\Http\Requests\UpdateProductVariantValueRequest;
use App\Http\Resources\ProductVariantValueResource;
use Illuminate\Http\Request;

class ProductVariantValueController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = ProductVariantValue::with('attributeValue.attribute');

            if ($request->has('product_variant_id')) {
                $query->where('product_variant_id', $request->product_variant_id);
            }

            $query->orderBy('created_at', 'desc');

            $data = $query->get();

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách giá trị biến thể thành công',
                'data' => ProductVariantValueResource::collection($data)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function store(StoreProductVariantValueRequest $request)
    {
        try {
            $data = $request->validated();

            $existing = ProductVariantValue::where('product_variant_id', $data['product_variant_id'])
                ->where('attribute_value_id', $data['attribute_value_id'])
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Biến thể này đã có giá trị thuộc tính này',
                    'data' => null
                ], 422);
            }

            $productVariantValue = ProductVariantValue::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Thêm giá trị biến thể thành công',
                'data' => new ProductVariantValueResource($productVariantValue)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function show(ProductVariantValue $productVariantValue)
    {
        try {
            $productVariantValue->load('attributeValue.attribute');
            return response()->json([
                'success' => true,
                'message' => 'Lấy chi tiết thành công',
                'data' => new ProductVariantValueResource($productVariantValue)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function update(UpdateProductVariantValueRequest $request, ProductVariantValue $productVariantValue)
    {
        try {
            $data = $request->validated();

            $existing = ProductVariantValue::where('product_variant_id', $data['product_variant_id'])
                ->where('attribute_value_id', $data['attribute_value_id'])
                ->where('id', '!=', $productVariantValue->id)
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Biến thể này đã có giá trị thuộc tính này',
                    'data' => null
                ], 422);
            }

            $productVariantValue->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thành công',
                'data' => new ProductVariantValueResource($productVariantValue)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function destroy(ProductVariantValue $productVariantValue)
    {
        try {
            $productVariantValue->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa thành công',
                'data' => null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
