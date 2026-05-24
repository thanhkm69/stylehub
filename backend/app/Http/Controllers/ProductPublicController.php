<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryPublicResource;
use App\Http\Resources\ProductDetailResource;
use App\Http\Resources\ProductListResource;
use App\Models\Category;
use App\Models\Combo;
use App\Models\FlashSale;
use App\Models\FlashSaleItem;
use App\Models\Product;
use App\Services\FlashSalePricingService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductPublicController extends Controller
{
    public function __construct(private FlashSalePricingService $flashSalePricingService) {}

    public function home(): JsonResponse
    {
        try {
            $data = Cache::remember('home_data', 600, function () {
                $newArrivals = Product::with(['category', 'activeImages'])
                    ->where('status', 1)
                    ->orderBy('created_at', 'desc')
                    ->limit(16)
                    ->get();

                $categories = Category::with('activeChildrenRecursive')
                    ->where('status', 1)
                    ->whereNull('parent_id')
                    ->orderBy('display', 'asc')
                    ->limit(8)
                    ->get();

                return [
                    'new_arrivals' => ProductListResource::collection($newArrivals),
                    'categories' => CategoryPublicResource::collection($categories),
                ];
            });

            // Active sales are time-sensitive and must not be held in the homepage cache.
            $data['flash_sales'] = FlashSale::query()
                ->where('status', 'active')
                ->where('starts_at', '<=', now())
                ->where('ends_at', '>=', now())
                ->with(['items' => function ($query) {
                    $query->where('status', 1)
                        ->orderBy('display')
                        ->with(['product' => function ($productQuery) {
                            $productQuery->with(['category', 'activeImages'])->where('status', 1);
                        }]);
                }])
                ->orderBy('display')
                ->orderBy('ends_at')
                ->get()
                ->map(function (FlashSale $flashSale) {
                    return [
                        'id' => $flashSale->id,
                        'name' => $flashSale->name,
                        'description' => $flashSale->description,
                        'thumbnail' => $flashSale->thumbnail,
                        'starts_at' => $flashSale->starts_at,
                        'ends_at' => $flashSale->ends_at,
                        'items' => $flashSale->items
                            ->filter(fn ($item) => $item->product !== null)
                            ->map(function ($item) {
                                return [
                                    'id' => $item->id,
                                    'flash_sale_id' => $item->flash_sale_id,
                                    'product_id' => $item->product_id,
                                    'product_variant_id' => $item->product_variant_id,
                                    'discount_type' => $item->discount_type,
                                    'discount_value' => $item->discount_value,
                                    'original_price' => $item->original_price,
                                    'sale_price' => $item->sale_price,
                                    'product' => new ProductListResource($item->product),
                                ];
                            })
                            ->values(),
                    ];
                })
                ->values();

            // Combos may enter or leave their configured validity window at any time.
            $data['combos'] = Combo::query()
                ->where('status', true)
                ->where(function ($query) {
                    $query->whereNull('starts_at')
                        ->orWhere('starts_at', '<=', now());
                })
                ->where(function ($query) {
                    $query->whereNull('ends_at')
                        ->orWhere('ends_at', '>=', now());
                })
                ->whereHas('items.product', function ($query) {
                    $query->where('status', 1);
                })
                ->with(['items' => function ($query) {
                    $query->orderBy('id')->with([
                        'product' => function ($productQuery) {
                            $productQuery->with(['category', 'activeImages'])->where('status', 1);
                        },
                        'productVariant',
                    ]);
                }])
                ->orderBy('display')
                ->orderByDesc('created_at')
                ->limit(6)
                ->get()
                ->map(function (Combo $combo) {
                    return [
                        'id' => $combo->id,
                        'name' => $combo->name,
                        'description' => $combo->description,
                        'thumbnail' => $combo->thumbnail,
                        'discount_type' => $combo->discount_type,
                        'discount_value' => (float) $combo->discount_value,
                        'ends_at' => $combo->ends_at,
                        'items' => $combo->items
                            ->filter(fn ($item) => $item->product !== null)
                            ->map(function ($item) {
                                return [
                                    'id' => $item->id,
                                    'quantity' => $item->quantity,
                                    'price' => (float) ($item->productVariant?->price ?? $item->product->price),
                                    'product' => new ProductListResource($item->product),
                                ];
                            })
                            ->values(),
                    ];
                })
                ->filter(fn ($combo) => $combo['items']->isNotEmpty())
                ->values();

            return response()->json([
                'success' => true,
                'message' => 'Lấy dữ liệu trang chủ thành công',
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: '.$e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function categories(): JsonResponse
    {
        $categories = Category::with('activeChildrenRecursive')
            ->where('status', 1)
            ->whereNull('parent_id')
            ->orderBy('display', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => CategoryPublicResource::collection($categories),
        ]);
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
                $query->where('name', 'like', '%'.$request->search.'%');
            }

            // Include every descendant when a parent category is selected.
            if ($request->filled('category_id')) {
                $query->whereIn('category_id', $this->categoryAndDescendantIds((int) $request->category_id));
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
                'price_asc' => ['price', 'asc'],
                'price_desc' => ['price', 'desc'],
            ];

            $sortKey = $request->input('sort', 'created_at_desc');
            $sort = $sortMap[$sortKey] ?? $sortMap['created_at_desc'];
            $query->orderBy($sort[0], $sort[1]);

            // Paginate (16 per page)
            $products = $query->paginate(16);
            $products->getCollection()->each(function (Product $product) {
                $product->setAttribute('pricing', $this->flashSalePricingService->forListing($product));
            });

            return ProductListResource::collection($products)->additional([
                'success' => true,
                'message' => 'Lấy danh sách sản phẩm thành công',
            ])->response()->setStatusCode(200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: '.$e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * GET /api/products/{product:slug}
     *
     * Product detail with views increment and related products.
     * Cached in Redis for 5 minutes (cache is busted on views increment).
     */
    public function show(Product $product): JsonResponse
    {
        try {
            // Increment views (outside cache)
            $product->increment('views');

            $cacheKey = 'product_'.$product->slug;

            $data = Cache::remember($cacheKey, 300, function () use ($product) {
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
                    $similarProducts = $aiRecommendations->filter(function ($p) {
                        return $p->pivot->type === 'similar';
                    })->values();

                    $outfitProducts = $aiRecommendations->filter(function ($p) {
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
            });

            // Flash Sale availability changes over time, so append it outside product cache.
            $flashSaleItems = FlashSaleItem::query()
                ->where('product_id', $product->id)
                ->where('status', 1)
                ->whereHas('flashSale', function ($query) {
                    $query->where('status', 'active')
                        ->where('starts_at', '<=', now())
                        ->where('ends_at', '>=', now());
                })
                ->with('flashSale')
                ->orderBy('display')
                ->get()
                ->map(function (FlashSaleItem $item) {
                    return [
                        'id' => $item->id,
                        'product_variant_id' => $item->product_variant_id,
                        'discount_type' => $item->discount_type,
                        'discount_value' => (float) $item->discount_value,
                        'original_price' => (float) $item->original_price,
                        'sale_price' => (float) $item->sale_price,
                        'flash_sale' => [
                            'id' => $item->flashSale->id,
                            'name' => $item->flashSale->name,
                            'ends_at' => $item->flashSale->ends_at,
                        ],
                    ];
                })
                ->values();

            $productData = $data instanceof ProductDetailResource ? $data->resolve() : $data;
            $productData['flash_sale_items'] = $flashSaleItems;

            return response()->json([
                'success' => true,
                'message' => 'Lấy chi tiết sản phẩm thành công',
                'data' => $productData,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: '.$e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    private function categoryAndDescendantIds(int $categoryId): array
    {
        $ids = [$categoryId];
        $parentIds = [$categoryId];

        while ($parentIds !== []) {
            $parentIds = Category::query()
                ->whereIn('parent_id', $parentIds)
                ->pluck('id')
                ->all();

            $ids = array_merge($ids, $parentIds);
        }

        return array_values(array_unique($ids));
    }
}
