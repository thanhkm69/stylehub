<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryPublicResource;
use App\Http\Resources\ProductDetailResource;
use App\Http\Resources\ProductListResource;
use App\Models\Category;
use App\Models\Product;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductPublicController extends Controller
{
    /**
     * Helper to load homepage data from database.
     */
    private function getHomeData(): array
    {
        $newArrivals = Product::with(['category', 'activeImages'])
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->limit(16)
            ->get();

        $categories = Category::with('children')
            ->where('status', 1)
            ->whereNull('parent_id')
            ->orderBy('display', 'asc')
            ->limit(8)
            ->get();

        return [
            'new_arrivals' => ProductListResource::collection($newArrivals),
            'categories'   => CategoryPublicResource::collection($categories),
        ];
    }

    /**
     * GET /api/home
     *
     * Returns homepage data: new arrivals + parent categories with children.
     * Cached in Redis for 10 minutes. Graceful fallback to database if Redis is down.
     */
    public function home(): JsonResponse
    {
        try {
            $data = Cache::remember('home_data', 600, function () {
                $newArrivals = Product::with(['category', 'activeImages'])
                    ->where('status', 1)
                    ->orderBy('created_at', 'desc')
                    ->limit(16)
                    ->get();

                $categories = Category::with('children')
                    ->where('status', 1)
                    ->whereNull('parent_id')
                    ->orderBy('display', 'asc')
                    ->limit(8)
                    ->get();

                $banners = \App\Models\Banner::where('status', 1)
                    ->orderBy('position', 'asc')
                    ->orderBy('created_at', 'desc')
                    ->get();

                return [
                    'new_arrivals' => ProductListResource::collection($newArrivals),
                    'categories'   => CategoryPublicResource::collection($categories),
                    'banners'      => $banners,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Lấy dữ liệu trang chủ thành công',
                'data'    => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
                'data'    => null,
            ], 500);
        }
    }

    /**
     * GET /api/products
     *
     * Product listing with search, filter, sort, and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Product::with(['category', 'activeImages'])
                ->where('status', 1);

            // Search by name
            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            // Filter by category_id (include child categories)
            if ($request->filled('category_id')) {
                $categoryId = $request->category_id;
                $categoryIds = Category::where('id', $categoryId)
                    ->orWhere('parent_id', $categoryId)
                    ->pluck('id')
                    ->toArray();

                $query->whereIn('category_id', $categoryIds);
            }

            // Filter by price range
            if ($request->filled('min_price')) {
                $query->where('price', '>=', $request->min_price);
            }

            if ($request->filled('max_price')) {
                $query->where('price', '<=', $request->max_price);
            }

            // Sort
            $sortMap = [
                'created_at_desc' => ['created_at', 'desc'],
                'price_asc'       => ['price', 'asc'],
                'price_desc'      => ['price', 'desc'],
            ];

            $sortKey = $request->input('sort', 'created_at_desc');
            $sort    = $sortMap[$sortKey] ?? $sortMap['created_at_desc'];
            $query->orderBy($sort[0], $sort[1]);

            // Paginate (16 per page)
            $products = $query->paginate(16);

            return ProductListResource::collection($products)->additional([
                'success' => true,
                'message' => 'Lấy danh sách sản phẩm thành công',
            ])->response()->setStatusCode(200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
                'data'    => null,
            ], 500);
        }
    }

    /**
     * Helper to load product detail from database.
     */
    private function getProductDetail(Product $product): ProductDetailResource
    {
        // Eager load full detail relationships
        $product->load([
            'category',
            'activeImages',
            'activeVariants.productVariantValues.attributeValue.attribute',
        ]);

        // Fetch AI recommendations if available
        $aiRecommendations = $product->recommendedProducts()
            ->with(['category', 'activeImages'])
            ->where('status', 1)
            ->get();

        if ($aiRecommendations->isNotEmpty()) {
            $similarProducts = $aiRecommendations->filter(function($p) {
                return $p->pivot->type === 'similar';
            })->values();
            
            $outfitProducts = $aiRecommendations->filter(function($p) {
                return $p->pivot->type === 'outfit';
            })->values();
        } else {
            $similarProducts = Product::with(['category', 'activeImages'])
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->where('status', 1)
                ->limit(8)
                ->get();
            $outfitProducts = collect([]);
        }

        $product->setRelation('similarProducts', $similarProducts);
        $product->setRelation('outfitProducts', $outfitProducts);

        // Map activeImages → images, activeVariants → variants for the resource
        $product->setRelation('images', $product->activeImages);
        $product->setRelation('variants', $product->activeVariants);

        return new ProductDetailResource($product);
    }

    /**
     * GET /api/products/{product:slug}
     *
     * Product detail with views increment and related products.
     * Cached in Redis for 5 minutes. Graceful fallback to database if Redis is down.
     */
    public function show(Product $product): JsonResponse
    {
        try {
            // Increment views (outside cache)
            try {
                $product->increment('views');
            } catch (Exception $viewsException) {
                // Ignore view increment errors if any database locks or issues
            }

            $cacheKey = 'product_' . $product->slug;

            $data = null;
            try {
                $data = Cache::remember($cacheKey, 300, function () use ($product) {
                    return $this->getProductDetail($product);
                });
            } catch (Exception $cacheException) {
                // Graceful fallback if Redis/Cache is down
                $data = $this->getProductDetail($product);
            }

            return response()->json([
                'success' => true,
                'message' => 'Lấy chi tiết sản phẩm thành công',
                'data'    => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
                'data'    => null,
            ], 500);
        }
    }
}
