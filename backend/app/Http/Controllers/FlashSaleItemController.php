<?php

namespace App\Http\Controllers;

use App\Models\FlashSaleItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Http\Requests\StoreFlashSaleItemRequest;
use App\Http\Requests\UpdateFlashSaleItemRequest;
use App\Http\Resources\FlashSaleItemResource;
use Illuminate\Http\Request;

class FlashSaleItemController extends Controller
{
    /**
     * Tính toán original_price và sale_price tự động.
     */
    private function computePrices(array $data): array
    {
        // Lấy giá gốc: ưu tiên variant → product
        if (!empty($data['product_variant_id'])) {
            $variant = ProductVariant::find($data['product_variant_id']);
            $originalPrice = $variant?->price ?? 0;
        } else {
            $product = Product::find($data['product_id']);
            $originalPrice = $product?->price ?? 0;
        }

        // Tính sale_price theo loại giảm giá
        $discountValue = (float) ($data['discount_value'] ?? 0);

        if ($data['discount_type'] === 'percentage') {
            $pct = min($discountValue, 100);
            $salePrice = max(0, $originalPrice - ($originalPrice * $pct / 100));
        } else {
            // fixed_price: giảm một khoản cố định
            $salePrice = max(0, $originalPrice - $discountValue);
        }

        return [
            'original_price' => round($originalPrice, 2),
            'sale_price'     => round($salePrice, 2),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = FlashSaleItem::with(['product', 'productVariant']);

            if ($request->has('flash_sale_id')) {
                $query->where('flash_sale_id', $request->flash_sale_id);
            }

            $sortMap = [
                'display_asc'     => ['display', 'asc'],
                'display_desc'    => ['display', 'desc'],
                'created_at_asc'  => ['created_at', 'asc'],
                'created_at_desc' => ['created_at', 'desc'],
            ];

            $sortKey = $request->sort ?? 'display_asc';
            $sort = $sortMap[$sortKey] ?? $sortMap['display_asc'];
            $query->orderBy($sort[0], $sort[1]);

            $data = $query->get();

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách sản phẩm flash sale thành công',
                'data'    => FlashSaleItemResource::collection($data),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server: ' . $e->getMessage(),
                'data'    => null,
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFlashSaleItemRequest $request)
    {
        try {
            $validated = $request->validated();

            // Tự tính giá gốc và giá sale
            $prices = $this->computePrices($validated);
            $validated['original_price'] = $prices['original_price'];
            $validated['sale_price']     = $prices['sale_price'];

            $flashSaleItem = FlashSaleItem::create($validated);
            $flashSaleItem->load(['product', 'productVariant']);

            return response()->json([
                'success' => true,
                'message' => 'Thêm sản phẩm vào flash sale thành công',
                'data'    => new FlashSaleItemResource($flashSaleItem),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server: ' . $e->getMessage(),
                'data'    => null,
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(FlashSaleItem $flashSaleItem)
    {
        try {
            $flashSaleItem->load(['product', 'productVariant']);
            return response()->json([
                'success' => true,
                'message' => 'Lấy chi tiết sản phẩm flash sale thành công',
                'data'    => new FlashSaleItemResource($flashSaleItem),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server: ' . $e->getMessage(),
                'data'    => null,
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFlashSaleItemRequest $request, FlashSaleItem $flashSaleItem)
    {
        try {
            $validated = $request->validated();

            // Tự tính lại giá gốc và giá sale mỗi khi update
            $prices = $this->computePrices($validated);
            $validated['original_price'] = $prices['original_price'];
            $validated['sale_price']     = $prices['sale_price'];

            $flashSaleItem->update($validated);
            $flashSaleItem->load(['product', 'productVariant']);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật sản phẩm flash sale thành công',
                'data'    => new FlashSaleItemResource($flashSaleItem),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server: ' . $e->getMessage(),
                'data'    => null,
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FlashSaleItem $flashSaleItem)
    {
        try {
            $flashSaleItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa sản phẩm flash sale thành công',
                'data'    => null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server: ' . $e->getMessage(),
                'data'    => null,
            ], 500);
        }
    }
}
