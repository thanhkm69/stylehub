<?php

namespace App\Http\Controllers;

use App\Models\ProductImage;
use App\Http\Requests\StoreProductImageRequest;
use App\Http\Requests\UpdateProductImageRequest;
use App\Http\Resources\ProductImageResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = ProductImage::query();

            if ($request->has('product_id')) {
                $query->where('product_id', $request->product_id);
            }

            $query->orderBy('display', 'asc');

            $data = $query->get();

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách hình ảnh thành công',
                'data' => ProductImageResource::collection($data)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductImageRequest $request)
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('uploads/product-images', 'public');
                $data['image'] = $path;
            }

            $productImage = ProductImage::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Thêm hình ảnh sản phẩm thành công',
                'data' => new ProductImageResource($productImage)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductImage $productImage)
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Lấy chi tiết hình ảnh thành công',
                'data' => new ProductImageResource($productImage)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductImageRequest $request, ProductImage $productImage)
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('image')) {
                if ($productImage->image && Storage::disk('public')->exists($productImage->image)) {
                    Storage::disk('public')->delete($productImage->image);
                }
                $path = $request->file('image')->store('uploads/product-images', 'public');
                $data['image'] = $path;
            }

            $productImage->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật hình ảnh thành công',
                'data' => new ProductImageResource($productImage)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductImage $productImage)
    {
        try {
            if ($productImage->image && Storage::disk('public')->exists($productImage->image)) {
                Storage::disk('public')->delete($productImage->image);
            }

            $productImage->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa hình ảnh thành công',
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
