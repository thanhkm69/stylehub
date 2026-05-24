<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Address;
use App\Models\ProductVariant;
use App\Services\GHNService;
use App\Http\Resources\CartResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\AddressResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmed;


class CheckoutController extends Controller
{
    protected $ghnService;

    public function __construct(GHNService $ghnService)
    {
        $this->ghnService = $ghnService;
    }

    public function index(): JsonResponse
    {
        $user = Auth::user();
        
        // Ensure relationships are loaded
        $cartItems = Cart::where('user_id', $user->id)
            ->with(['product.category', 'productVariant.productVariantValues.attributeValue.attribute'])
            ->get();

        $addresses = Address::where('user_id', $user->id)
            ->orderBy('is_default', 'desc')
            ->get();
            
        $defaultAddress = $addresses->where('is_default', true)->first();

        // Calculate initial shipping fee
        $shippingFee = 0;
        if ($defaultAddress) {
            $shippingFee = $this->calculateShippingFee($defaultAddress, $cartItems);
        } elseif ($addresses->isNotEmpty()) {
            $shippingFee = $this->calculateShippingFee($addresses->first(), $cartItems);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy dữ liệu checkout thành công',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'cart_items' => CartResource::collection($cartItems),
                'addresses' => AddressResource::collection($addresses),
                'default_address_id' => $defaultAddress ? $defaultAddress->id : ($addresses->first() ? $addresses->first()->id : null),
                'initial_shipping_fee' => $shippingFee,
                'cart_summary' => $this->getCartSummary($cartItems)
            ]
        ]);
    }

    public function preview(Request $request): JsonResponse
    {
        $request->validate(['address_id' => 'required|exists:addresses,id']);
        $user = Auth::user();
        $address = Address::findOrFail($request->address_id);
        
        if ($address->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Địa chỉ không hợp lệ.'], 403);
        }

        $cartItems = Cart::where('user_id', $user->id)->get();
        $shippingFee = $this->calculateShippingFee($address, $cartItems);

        return response()->json([
            'success' => true,
            'data' => ['shipping_fee' => $shippingFee]
        ]);
    }

    public function process(Request $request): JsonResponse
    {
        $request->validate([
            'address_id' => 'required|exists:addresses,id',
            'payment_method' => 'required|in:cod,vnpay,momo',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'required|email|max:255',
            'note' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $address = Address::findOrFail($request->address_id);
        
        if ($address->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Địa chỉ không hợp lệ.'], 403);
        }

        $cartItems = Cart::where('user_id', $user->id)
            ->with(['product', 'productVariant'])
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Giỏ hàng trống.'], 422);
        }

        DB::beginTransaction();
        try {
            $shippingFee = $this->calculateShippingFee($address, $cartItems);
            $cartSummary = $this->getCartSummary($cartItems);
            
            $subtotal = $cartSummary['total_price'];
            $discount = 0;
            $total = $subtotal + $shippingFee - $discount;

            $order = Order::create([
                'user_id' => $user->id,
                'order_code' => 'ORD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3))),
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                'payment_status' => 'unpaid',
                'subtotal_amount' => $subtotal,
                'discount_amount' => $discount,
                'shipping_fee' => $shippingFee,
                'total_amount' => $total,
                'shipping_info' => [
                    'name' => $request->customer_name,
                    'phone' => $request->customer_phone,
                    'email' => $request->customer_email,
                    'address' => $address->address,
                    'province' => $address->province_name,
                    'district' => $address->district_name,
                    'ward' => $address->ward_name,
                ],
                'note' => $request->note,
            ]);

            foreach ($cartItems as $item) {
                $price = $item->product_variant_id ? $item->productVariant->price : $item->product->price;
                
                $order->orderDetails()->create([
                    'product_id' => $item->product_id,
                    'variant_id' => $item->product_variant_id,
                    'product_name' => $item->product->name,
                    'variant_name' => $item->product_variant_id ? $this->getVariantName($item->productVariant) : null,
                    'sku' => $item->product_variant_id ? $item->productVariant->sku : $item->product->sku,
                    'price' => $price,
                    'quantity' => $item->quantity,
                    'subtotal' => $price * $item->quantity,
                ]);

                if ($item->product_variant_id) {
                    $item->productVariant->decrement('stock', $item->quantity);
                }
            }

            Cart::where('user_id', $user->id)->delete();
            DB::commit();

            // Send confirmation email
            try {
                Mail::to($order->shipping_email)->send(new OrderConfirmed($order));
            } catch (\Exception $e) {
                Log::error('Gửi mail xác nhận đơn hàng thất bại: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Đặt hàng thành công!',
                'data' => [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function calculateShippingFee($address, $cartItems)
    {
        $totalWeight = 0;
        foreach ($cartItems as $item) {
            $totalWeight += (200 * $item->quantity);
        }

        $res = $this->ghnService->calculateShippingFee([
            'to_district_id' => (int) $address->district_id,
            'to_ward_code' => (string) $address->ward_code,
            'weight' => max($totalWeight, 200),
            'length' => 20,
            'width' => 20,
            'height' => 10,
            'service_type_id' => 2,
        ]);

        return $res ? ($res['total'] ?? 30000) : 30000;
    }

    private function getCartSummary($cartItems): array
    {
        $totalItems = 0;
        $totalPrice = 0;
        foreach ($cartItems as $item) {
            $totalItems += $item->quantity;
            $price = $item->product_variant_id ? $item->productVariant?->price : $item->product?->price;
            $totalPrice += ($price * $item->quantity);
        }
        return ['total_items' => (int) $totalItems, 'total_price' => (float) $totalPrice];
    }

    private function getVariantName($variant): string
    {
        return $variant->productVariantValues->map(function($pvv) {
            return $pvv->attributeValue->attribute->name . ': ' . $pvv->attributeValue->value;
        })->implode(', ');
    }
}
