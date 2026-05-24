<?php

namespace App\Http\Controllers;

use App\Http\Resources\AddressResource;
use App\Http\Resources\CartResource;
use App\Mail\OrderConfirmed;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Services\ComboPricingService;
use App\Services\CouponPricingService;
use App\Services\FlashSalePricingService;
use App\Services\GHNService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    protected $ghnService;

    public function __construct(
        GHNService $ghnService,
        private FlashSalePricingService $flashSalePricingService,
        private ComboPricingService $comboPricingService,
        private CouponPricingService $couponPricingService
    ) {
        $this->ghnService = $ghnService;
    }

    public function index(): JsonResponse
    {
        $user = Auth::user();

        // Ensure relationships are loaded
        $cartItems = Cart::where('user_id', $user->id)
            ->with(['product.category', 'productVariant.productVariantValues.attributeValue.attribute'])
            ->get();
        $this->applyFlashSalePricing($cartItems);

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
                'cart_summary' => $this->getCartSummary($cartItems),
            ],
        ]);
    }

    public function preview(Request $request): JsonResponse
    {
        $request->validate([
            'address_id' => 'required|exists:addresses,id',
            'coupon_code' => 'nullable|string|max:50',
        ]);
        $user = Auth::user();
        $address = Address::findOrFail($request->address_id);

        if ($address->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Địa chỉ không hợp lệ.'], 403);
        }

        $cartItems = Cart::where('user_id', $user->id)
            ->with(['product', 'productVariant'])
            ->get();
        $this->applyFlashSalePricing($cartItems);
        $shippingFee = $this->calculateShippingFee($address, $cartItems);
        $cartSummary = $this->getCartSummary($cartItems);
        $couponPricing = $this->couponPricingService->calculate(
            $request->input('coupon_code'),
            $cartSummary['total_price']
        );

        return response()->json([
            'success' => true,
            'data' => [
                'shipping_fee' => $shippingFee,
                'coupon' => $couponPricing['coupon'],
                'coupon_discount' => $couponPricing['discount_amount'],
            ],
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
            'coupon_code' => 'nullable|string|max:50',
        ]);

        $user = Auth::user();
        $address = Address::findOrFail($request->address_id);

        if ($address->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Địa chỉ không hợp lệ.'], 403);
        }

        $cartItems = Cart::where('user_id', $user->id)
            ->with(['product', 'productVariant'])
            ->get();
        $this->applyFlashSalePricing($cartItems);

        if ($cartItems->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Giỏ hàng trống.'], 422);
        }

        DB::beginTransaction();
        try {
            $shippingFee = $this->calculateShippingFee($address, $cartItems);
            $cartSummary = $this->getCartSummary($cartItems);
            $couponPricing = $this->couponPricingService->calculate(
                $request->input('coupon_code'),
                $cartSummary['total_price'],
                true
            );

            $subtotal = $cartSummary['subtotal_before_combo'];
            $discount = $cartSummary['combo_discount'] + $couponPricing['discount_amount'];
            $total = max($subtotal - $discount, 0) + $shippingFee;

            $order = Order::create([
                'user_id' => $user->id,
                'coupon_id' => $couponPricing['coupon_id'],
                'order_code' => 'ORD-'.date('Ymd').'-'.strtoupper(bin2hex(random_bytes(3))),
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
                $price = $item->getAttribute('pricing')['price'];

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
                Log::error('Gửi mail xác nhận đơn hàng thất bại: '.$e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Đặt hàng thành công!',
                'data' => [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                ],
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();

            throw $e;
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
        $originalTotalPrice = 0;

        foreach ($cartItems as $item) {
            $totalItems += $item->quantity;
            $pricing = $item->getAttribute('pricing');
            $price = $pricing['price'];
            $totalPrice += ($price * $item->quantity);
            $originalTotalPrice += ($pricing['original_price'] * $item->quantity);
        }

        $comboPricing = $this->comboPricingService->calculate($cartItems);

        return [
            'total_items' => (int) $totalItems,
            'total_price' => (float) max($totalPrice - $comboPricing['combo_discount'], 0),
            'original_total_price' => (float) $originalTotalPrice,
            'flash_sale_savings' => (float) ($originalTotalPrice - $totalPrice),
            'subtotal_before_combo' => (float) $totalPrice,
            'combo_discount' => $comboPricing['combo_discount'],
            'applied_combos' => $comboPricing['applied_combos'],
            'applied_combo' => $comboPricing['applied_combo'],
            'coupon_discount' => 0.0,
            'coupon' => null,
        ];
    }

    private function applyFlashSalePricing($cartItems): void
    {
        $cartItems->each(function (Cart $item) {
            $item->setAttribute(
                'pricing',
                $this->flashSalePricingService->forSelection($item->product, $item->productVariant)
            );
        });
    }

    private function getVariantName($variant): string
    {
        return $variant->productVariantValues->map(function ($pvv) {
            return $pvv->attributeValue->attribute->name.': '.$pvv->attributeValue->value;
        })->implode(', ');
    }
}
