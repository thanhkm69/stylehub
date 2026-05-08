<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Http\Requests\StoreProductVariantRequest;
use App\Http\Requests\UpdateProductVariantRequest;
use App\Http\Resources\ProductVariantResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductVariantController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = ProductVariant::withCount('productVariantValues');

            if ($request->has('product_id')) {
                $query->where('product_id', $request->product_id);
            }

            if ($request->filled('search')) {
                $query->where('sku', 'like', '%' . $request->search . '%');
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $sortMap = [
                'price_asc' => ['price', 'asc'],
                'price_desc' => ['price', 'desc'],
                'created_at_asc' => ['created_at', 'asc'],
                'created_at_desc' => ['created_at', 'desc'],
            ];

            $sortKey = $request->sort ?? 'created_at_desc';
            $sort = $sortMap[$sortKey] ?? $sortMap['created_at_desc'];
            $query->orderBy($sort[0], $sort[1]);

            $limit = $request->input('limit', 15);
            $data = $query->paginate((int)$limit);

            return ProductVariantResource::collection($data)->additional([
                'success' => true,
                'message' => 'Lấy danh sách biến thể thành công',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function store(StoreProductVariantRequest $request)
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('uploads/product-variants', 'public');
                $data['image'] = $path;
            }

            $productVariant = ProductVariant::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Thêm biến thể sản phẩm thành công',
                'data' => new ProductVariantResource($productVariant)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function show(ProductVariant $productVariant)
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Lấy chi tiết biến thể thành công',
                'data' => new ProductVariantResource($productVariant)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function update(UpdateProductVariantRequest $request, ProductVariant $productVariant)
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('image')) {
                if ($productVariant->image && Storage::disk('public')->exists($productVariant->image)) {
                    Storage::disk('public')->delete($productVariant->image);
                }
                $path = $request->file('image')->store('uploads/product-variants', 'public');
                $data['image'] = $path;
            }

            $productVariant->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật biến thể thành công',
                'data' => new ProductVariantResource($productVariant)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function destroy(ProductVariant $productVariant)
    {
        try {
            if ($productVariant->image && Storage::disk('public')->exists($productVariant->image)) {
                Storage::disk('public')->delete($productVariant->image);
            }

            $productVariant->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa biến thể thành công',
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
