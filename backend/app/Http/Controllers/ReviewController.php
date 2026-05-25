<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Order;
use App\Models\Review;
use App\Services\ContentFilterService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    public function __construct(
        protected ContentFilterService $filterService
    ) {}

    /**
     * Display a listing of reviews (public).
     */
    public function index(Request $request)
    {
        try {
            $query = Review::with(['user', 'product']);

            // Filter status: public defaults to status = 1 (active/approved) unless explicitly specified
            if ($request->filled('status')) {
                $query->where('status', (int) $request->status);
            } else {
                $query->where('status', 1);
            }

            if ($request->has('product_id')) {
                $query->where('product_id', $request->product_id);
            }

            if ($request->has('rating')) {
                $query->where('rating', $request->rating);
            }

            $reviews = $query->orderBy('created_at', 'desc')->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách đánh giá thành công',
                'data' => ReviewResource::collection($reviews),
                'pagination' => [
                    'current_page' => $reviews->currentPage(),
                    'last_page' => $reviews->lastPage(),
                    'per_page' => $reviews->perPage(),
                    'total' => $reviews->total(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: '.$e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Store a newly created review in storage.
     */
    public function store(StoreReviewRequest $request)
    {
        try {
            $userId = Auth::id();
            $productId = $request->product_id;
            $orderId = $request->order_id;

            // 1. Validate: order must belong to user, have delivered status, and contain the product
            $order = Order::where('id', $orderId)
                ->where('user_id', $userId)
                ->where('status', 'delivered')
                ->whereHas('orderDetails', function ($q) use ($productId) {
                    $q->where('product_id', $productId);
                })
                ->first();

            if (! $order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn chỉ có thể đánh giá sản phẩm đã mua và đã được giao thành công.',
                    'data' => null,
                ], 403);
            }

            // 2. Validate: user hasn't already reviewed this product in this order
            $alreadyReviewed = Review::where('user_id', $userId)
                ->where('product_id', $productId)
                ->where('order_id', $orderId)
                ->exists();

            if ($alreadyReviewed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn đã đánh giá sản phẩm này cho đơn hàng này rồi.',
                    'data' => null,
                ], 400);
            }

            // 3. Process image upload if any
            $imagePaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $path = $file->store('uploads/reviews', 'public');
                    $imagePaths[] = $path;
                }
            }

            // 4. Create review
            $hasBadContent = $this->filterService->hasBadContent($request->comment);

            $review = Review::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'order_id' => $orderId,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'images' => $imagePaths,
                'status' => $hasBadContent ? 2 : 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => $hasBadContent
                    ? 'Đánh giá của bạn đang chờ duyệt do chứa từ ngữ không phù hợp. Vui lòng chỉnh sửa nội dung để hiển thị công khai.'
                    : 'Đánh giá sản phẩm thành công',
                'data' => new ReviewResource($review->load(['user', 'product'])),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: '.$e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Display the specified review.
     */
    public function show(Review $review)
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Lấy chi tiết đánh giá thành công',
                'data' => new ReviewResource($review->load(['user', 'product'])),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: '.$e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Update the specified review in storage.
     */
    public function update(UpdateReviewRequest $request, Review $review)
    {
        try {
            $user = Auth::user();

            // Authorize: only owner or Admin can edit
            if ($review->user_id !== $user->id && $user->role !== 'Admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền thực hiện hành động này.',
                    'data' => null,
                ], 403);
            }

            $data = $request->validated();

            // Process image upload if any, and remove old images
            if ($request->hasFile('images')) {
                // Delete old images
                if (! empty($review->images)) {
                    foreach ($review->images as $oldImage) {
                        if (Storage::disk('public')->exists($oldImage)) {
                            Storage::disk('public')->delete($oldImage);
                        }
                    }
                }

                $imagePaths = [];
                foreach ($request->file('images') as $file) {
                    $path = $file->store('uploads/reviews', 'public');
                    $imagePaths[] = $path;
                }
                $data['images'] = $imagePaths;
            }

            // Determine the final comment that will be stored
            $finalComment = $data['comment'] ?? $review->comment;
            $hasBadContent = $this->filterService->hasBadContent($finalComment);

            // Handle status
            if ($user->role === 'Admin') {
                if ($request->has('status')) {
                    $data['status'] = (int) $request->status;
                } else {
                    $data['status'] = $hasBadContent ? 2 : 1;
                }
            } else {
                $data['status'] = $hasBadContent ? 2 : 1;
            }

            $review->update($data);

            // Determine response message
            if ($user->role !== 'Admin' && $hasBadContent) {
                $message = 'Đánh giá của bạn đang chờ duyệt do chứa từ ngữ không phù hợp. Vui lòng chỉnh sửa nội dung để hiển thị công khai.';
            } else {
                $message = 'Cập nhật đánh giá thành công';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => new ReviewResource($review->load(['user', 'product'])),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: '.$e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Remove the specified review from storage.
     */
    public function destroy(Review $review)
    {
        try {
            $user = Auth::user();

            // Authorize: owner or Admin
            if ($review->user_id !== $user->id && $user->role !== 'Admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền thực hiện hành động này.',
                    'data' => null,
                ], 403);
            }

            // Delete images from disk
            if (! empty($review->images)) {
                foreach ($review->images as $image) {
                    if (Storage::disk('public')->exists($image)) {
                        Storage::disk('public')->delete($image);
                    }
                }
            }

            $review->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa đánh giá thành công',
                'data' => null,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: '.$e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Display a listing of reviews for Admin.
     */
    public function adminIndex(Request $request)
    {
        try {
            $query = Review::with(['user', 'product']);

            // Search by user name or product name
            if ($request->has('search') && ! empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%");
                    })->orWhereHas('product', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%");
                    });
                });
            }

            // Filters
            if ($request->has('product_id')) {
                $query->where('product_id', $request->product_id);
            }

            if ($request->filled('rating')) {
                $query->where('rating', $request->rating);
            }

            if ($request->filled('status')) {
                $query->where('status', (int) $request->status);
            }

            $reviews = $query->orderBy('created_at', 'desc')->paginate(15);

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách đánh giá cho admin thành công',
                'data' => ReviewResource::collection($reviews),
                'pagination' => [
                    'current_page' => $reviews->currentPage(),
                    'last_page' => $reviews->lastPage(),
                    'per_page' => $reviews->perPage(),
                    'total' => $reviews->total(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: '.$e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Toggle the status of a review (admin).
     */
    public function adminToggleStatus(Review $review)
    {
        try {
            $review->status = ($review->status == 1) ? 0 : 1;
            $review->save();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái thành công',
                'data' => new ReviewResource($review->load(['user', 'product'])),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: '.$e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}
