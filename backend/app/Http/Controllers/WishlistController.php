<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use Illuminate\Http\Request;
use \App\Http\Resources\WishlistResource;
use \App\Models\Product;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $wishlists = Wishlist::with(['product.category', 'product.images'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(16);

        return WishlistResource::collection($wishlists)->additional([
            'success' => true,
            'message' => 'Lấy danh sách yêu thích thành công',
        ]);
    }

    public function ids(Request $request)
    {
        $ids = Wishlist::where('user_id', $request->user()->id)->pluck('product_id');
        return response()->json([
            'success' => true,
            'data' => $ids,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $product = Product::find($request->product_id);
        if (!$product || $product->status != 1) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại hoặc đã ngừng kinh doanh',
                'data' => null,
            ], 400);
        }

        $exists = Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $request->product_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm đã có trong danh sách yêu thích',
                'data' => null,
            ], 400);
        }

        $wishlist = Wishlist::create([
            'user_id' => $request->user()->id,
            'product_id' => $request->product_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã thêm vào yêu thích',
            'data' => new WishlistResource($wishlist),
        ]);
    }

    public function destroy(Request $request, Wishlist $wishlist)
    {
        if ($wishlist->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Không có quyền xóa',
                'data' => null,
            ], 403);
        }

        $wishlist->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa khỏi yêu thích',
            'data' => null,
        ]);
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $product = Product::find($request->product_id);
        if (!$product || $product->status != 1) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại',
                'data' => null,
            ], 400);
        }

        $wishlist = Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($wishlist) {
            $wishlist->delete();
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa khỏi yêu thích',
                'status' => 'removed',
                'data' => null,
            ]);
        } else {
            $wishlist = Wishlist::create([
                'user_id' => $request->user()->id,
                'product_id' => $request->product_id,
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Đã thêm vào yêu thích',
                'status' => 'added',
                'data' => new WishlistResource($wishlist),
            ]);
        }
    }
}
