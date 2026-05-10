<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $products = Product::with('category')->withCount(['images', 'variants']);

            if ($request->filled('search')) {
                $products->where('name', 'like', '%' . $request->search . '%');
            }

            if ($request->filled('status')) {
                $products->where('status', $request->status);
            }

            $sortMap = [
                'price_asc' => ['price', 'asc'],
                'price_desc' => ['price', 'desc'],
                'created_at_asc' => ['created_at', 'asc'],
                'created_at_desc' => ['created_at', 'desc'],
            ];

            $sortKey = $request->sort ?? 'created_at_desc';
            $sort = $sortMap[$sortKey] ?? $sortMap['created_at_desc'];
            $products->orderBy($sort[0], $sort[1]);

            if ($request->has('limit')) {
                $limit = $request->input('limit');
                $products = $products->paginate((int)$limit);
            } else {
                $products = $products->get();
            }

            return ProductResource::collection($products)->additional([
                'success' => true,
                'message' => 'Lấy danh sách sản phẩm thành công',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        try {
            $validated = $request->validated();

            if ($request->hasFile('thumbnail')) {
                $path = $request->file('thumbnail')->store('uploads/products', 'public');
                $validated['thumbnail'] = $path;
            }

            $product = Product::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Thêm sản phẩm thành công',
                'data' => new ProductResource($product)
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Lấy thông tin sản phẩm thành công',
                'data' => new ProductResource($product)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        try {
            $validated = $request->validated();

            if ($request->hasFile('thumbnail')) {
                $path = $request->file('thumbnail')->store('uploads/products', 'public');
                if ($path) {
                    Storage::disk('public')->delete($product->thumbnail);
                }
                $validated['thumbnail'] = $path;
            }

            $product->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật sản phẩm thành công',
                'data' => new ProductResource($product)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            if ($product->thumbnail) {
                Storage::disk('public')->delete($product->thumbnail);
            }
            $product->delete();
            return response()->json([
                'success' => true,
                'message' => 'Xóa sản phẩm thành công',
                'data' => null
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
