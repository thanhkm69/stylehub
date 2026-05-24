<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCartRequest;
use App\Http\Requests\UpdateCartRequest;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\ComboPricingService;
use App\Services\FlashSalePricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function __construct(
        private FlashSalePricingService $flashSalePricingService,
        private ComboPricingService $comboPricingService
    ) {}

    public function index(): JsonResponse
    {
        $user = Auth::user();
        $cartItems = $this->loadCartItems($user->id);

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách giỏ hàng thành công',
            'data' => CartResource::collection($cartItems),
            'cart_summary' => $this->getCartSummary($cartItems),
        ]);
    }

    public function store(StoreCartRequest $request): JsonResponse
    {
        $user = Auth::user();
        $productId = $request->product_id;
        $variantId = $request->product_variant_id;
        $quantity = $request->quantity;
        $product = Product::find($productId);
        if (! $product || $product->status != 1) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không khả dụng.',
            ], 422);
        }

        if ($variantId) {
            $variant = ProductVariant::find($variantId);
            if (! $variant || $variant->status != 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Biến thể sản phẩm không khả dụng.',
                ], 422);
            }
            if ($variant->stock < $quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Số lượng trong kho không đủ.',
                ], 422);
            }
        } else {

        }

        $cart = Cart::where([
            'user_id' => $user->id,
            'product_id' => $productId,
            'product_variant_id' => $variantId,
        ])->first();

        if ($cart) {
            $cart->increment('quantity', $quantity);
        } else {
            $cart = Cart::create([
                'user_id' => $user->id,
                'product_id' => $productId,
                'product_variant_id' => $variantId,
                'quantity' => $quantity,
            ]);
        }

        $cart->refresh();
        if ($cart->quantity > 99) {
            $cart->update(['quantity' => 99]);
        }

        if ($variantId) {
            $variant = ProductVariant::find($variantId);
            if ($cart->quantity > $variant->stock) {
                $cart->update(['quantity' => $variant->stock]);
            }
        }

        $cartItems = $this->loadCartItems($user->id);
        $cartItem = $cartItems->firstWhere('id', $cart->id);

        return response()->json([
            'success' => true,
            'message' => 'Đã thêm vào giỏ hàng thành công',
            'data' => new CartResource($cartItem),
            'cart_summary' => $this->getCartSummary($cartItems),
        ]);
    }

    public function update(UpdateCartRequest $request, Cart $cart): JsonResponse
    {
        if ($cart->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Hành động không được phép.',
            ], 403);
        }

        $quantity = $request->quantity;

        // Strict Stock Check
        if ($cart->product_variant_id) {
            $variant = ProductVariant::find($cart->product_variant_id);
            if (! $variant || $variant->stock < $quantity) {
                return response()->json([
                    'success' => false,
                    'message' => "Số lượng trong kho không đủ (Còn lại: {$variant->stock})",
                ], 422);
            }
        }

        $cart->update(['quantity' => $quantity]);

        $cartItems = $this->loadCartItems(Auth::id());
        $cartItem = $cartItems->firstWhere('id', $cart->id);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật số lượng thành công',
            'data' => new CartResource($cartItem),
            'cart_summary' => $this->getCartSummary($cartItems),
        ]);
    }

    public function destroy(Cart $cart): JsonResponse
    {
        if ($cart->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Hành động không được phép.',
            ], 403);
        }

        $cart->delete();

        $cartItems = $this->loadCartItems(Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa sản phẩm khỏi giỏ hàng',
            'cart_summary' => $this->getCartSummary($cartItems),
        ]);
    }

    public function clear(): JsonResponse
    {
        Cart::where('user_id', Auth::id())->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã làm trống giỏ hàng',
            'cart_summary' => [
                'total_items' => 0,
                'total_price' => 0,
                'original_total_price' => 0,
                'flash_sale_savings' => 0,
                'subtotal_before_combo' => 0,
                'combo_discount' => 0,
                'applied_combos' => [],
                'applied_combo' => null,
            ],
        ]);
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
        ];
    }

    private function loadCartItems(int $userId)
    {
        $cartItems = Cart::where('user_id', $userId)
            ->with([
                'product.category',
                'product.images',
                'productVariant.productVariantValues.attributeValue.attribute',
            ])
            ->get();

        $cartItems->each(function (Cart $item) {
            $item->setAttribute(
                'pricing',
                $this->flashSalePricingService->forSelection($item->product, $item->productVariant)
            );
        });

        return $cartItems;
    }
}
