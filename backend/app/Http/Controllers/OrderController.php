<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OrderController extends Controller
{
    /**
     * Display a listing of the orders (Admin).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::query()->with(['user', 'coupon', 'orderDetails.product', 'orderDetails.productVariant']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                  ->orWhere('shipping_name', 'like', "%{$search}%")
                  ->orWhere('shipping_phone', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách đơn hàng thành công',
            'data' => OrderResource::collection($orders),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
            ]
        ]);
    }

    /**
     * Store a newly created order.
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            // Generate Order Code: ORD-YYYYMMDD-XXXXX
            $orderCode = 'ORD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
            
            $subtotalAmount = 0;
            $itemsData = [];

            // Calculate subtotal and prepare data
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $variant = isset($item['variant_id']) 
                    ? ProductVariant::with('productVariantValues.attributeValue.attribute')->find($item['variant_id']) 
                    : null;
                
                $price = $variant ? $variant->price : $product->price;
                $lineSubtotal = $price * $item['quantity'];
                $subtotalAmount += $lineSubtotal;

                $itemsData[] = [
                    'product_id' => $product->id,
                    'variant_id' => $variant ? $variant->id : null,
                    'product_name' => $product->name,
                    'variant_name' => $variant ? $this->getVariantName($variant) : null,
                    'sku' => $variant ? $variant->sku : $product->sku, // Assuming product has sku
                    'price' => $price,
                    'quantity' => $item['quantity'],
                    'subtotal' => $lineSubtotal,
                ];
            }

            // Simple discount calculation (can be expanded with Coupon logic later)
            $discountAmount = 0; // Logic for coupon would go here
            $shippingFee = $request->shipping_fee ?? 0;
            $totalAmount = $subtotalAmount - $discountAmount + $shippingFee;

            $order = Order::create(array_merge($request->validated(), [
                'order_code' => $orderCode,
                'subtotal_amount' => $subtotalAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
            ]));

            // Save order details
            foreach ($itemsData as $detail) {
                $order->orderDetails()->create($detail);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tạo đơn hàng thành công',
                'data' => new OrderResource($order->load('orderDetails'))
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order Store Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo đơn hàng: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết đơn hàng thành công',
            'data' => new OrderResource($order->load(['user', 'coupon', 'orderDetails.product', 'orderDetails.productVariant']))
        ]);
    }

    /**
     * Update the specified order (Admin).
     */
    public function update(UpdateOrderRequest $request, Order $order): JsonResponse
    {
        $data = $request->validated();

        // Handle Status Timestamps
        if (isset($data['status']) && $data['status'] !== $order->status) {
            switch ($data['status']) {
                case 'confirmed': $data['confirmed_at'] = now(); break;
                case 'shipping': $data['shipped_at'] = now(); break;
                case 'delivered': $data['delivered_at'] = now(); $data['payment_status'] = 'paid'; break;
                case 'cancelled': $data['cancelled_at'] = now(); break;
            }
        }

        $order->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật đơn hàng thành công',
            'data' => new OrderResource($order->fresh(['user', 'orderDetails']))
        ]);
    }

    /**
     * Remove the specified order.
     */
    public function destroy(Order $order): JsonResponse
    {
        if ($order->status !== 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể xóa đơn hàng đã bị hủy.',
            ], 422);
        }

        $order->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa đơn hàng thành công'
        ]);
    }

    /**
     * Display order by code.
     */
    public function showByCode(string $code): JsonResponse
    {
        $order = Order::where('order_code', $code)
            ->with(['user', 'coupon', 'orderDetails.product', 'orderDetails.productVariant'])
            ->firstOrFail();

        // Security check: only owner or admin
        if ($order->user_id !== auth()->id() && !auth()->user()->tokenCan('Admin')) {
            return response()->json(['success' => false, 'message' => 'Hành động không được phép.'], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết đơn hàng thành công',
            'data' => new OrderResource($order)
        ]);
    }

    /**
     * Helper to get variant name string.
     */
    private function getVariantName($variant): string
    {
        // Adjust this based on how your variant values are structured
        // Example: "Color: Red, Size: L"
        return $variant->productVariantValues->map(function($pvv) {
            return $pvv->attributeValue->attribute->name . ': ' . $pvv->attributeValue->value;
        })->implode(', ');
    }
}
