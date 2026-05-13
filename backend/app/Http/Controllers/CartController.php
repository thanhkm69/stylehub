<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCartRequest;
use App\Http\Requests\UpdateCartRequest;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $cartItems = Cart::where('user_id', $user->id)
            ->with([
                'product.category', 
                'product.images', 
                'productVariant.productVariantValues.attributeValue.attribute'
            ])
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách giỏ hàng thành công',
            'data' => CartResource::collection($cartItems),
            'cart_summary' => $this->getCartSummary($cartItems)
        ]);
    }

    public function store(StoreCartRequest $request): JsonResponse
    {
        $user = Auth::user();
        $productId = $request->product_id;
        $variantId = $request->product_variant_id;
        $quantity = $request->quantity;
        $product = Product::find($productId);
        if (!$product || $product->status != 1) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không khả dụng.',
            ], 422);
        }

        if ($variantId) {
            $variant = ProductVariant::find($variantId);
            if (!$variant || $variant->status != 1) {
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

        $cartItems = Cart::where('user_id', $user->id)->get();

        return response()->json([
            'success' => true,
            'message' => 'Đã thêm vào giỏ hàng thành công',
            'data' => new CartResource($cart->load([
                'product.category', 
                'productVariant.productVariantValues.attributeValue.attribute'
            ])),
            'cart_summary' => $this->getCartSummary($cartItems)
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
            if (!$variant || $variant->stock < $quantity) {
                return response()->json([
                    'success' => false,
                    'message' => "Số lượng trong kho không đủ (Còn lại: {$variant->stock})",
                ], 422);
            }
        }

        $cart->update(['quantity' => $quantity]);
        
        $cartItems = Cart::where('user_id', Auth::id())->get();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật số lượng thành công',
            'data' => new CartResource($cart->load([
                'product.category', 
                'productVariant.productVariantValues.attributeValue.attribute'
            ])),
            'cart_summary' => $this->getCartSummary($cartItems)
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

        $cartItems = Cart::where('user_id', Auth::id())->get();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa sản phẩm khỏi giỏ hàng',
            'cart_summary' => $this->getCartSummary($cartItems)
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
                'total_price' => 0
            ]
        ]);
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

        return [
            'total_items' => (int) $totalItems,
            'total_price' => (float) $totalPrice
        ];
    }
}
