<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Http\Requests\StoreCouponRequest;
use App\Http\Requests\UpdateCouponRequest;
use App\Http\Resources\CouponResource;
use Illuminate\Http\Request;
use Exception;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $coupons = Coupon::query();

            if ($request->filled('search')) {
                $coupons->where(function ($q) use ($request) {
                    $q->where('code', 'like', '%' . $request->search . '%')
                      ->orWhere('name', 'like', '%' . $request->search . '%');
                });
            }

            if ($request->filled('status')) {
                $coupons->where('status', $request->status);
            }

            if ($request->filled('discount_type')) {
                $coupons->where('discount_type', $request->discount_type);
            }

            $sortMap = [
                'created_at_asc'  => ['created_at', 'asc'],
                'created_at_desc' => ['created_at', 'desc'],
            ];

            $sortKey = $request->sort ?? 'created_at_desc';
            $sort = $sortMap[$sortKey] ?? $sortMap['created_at_desc'];
            $coupons->orderBy($sort[0], $sort[1]);

            $limit = $request->input('limit', 15);
            $coupons = $coupons->paginate((int) $limit);

            return CouponResource::collection($coupons)->additional([
                'success' => true,
                'message' => 'Lấy danh sách mã giảm giá thành công',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCouponRequest $request)
    {
        try {
            $data = $request->validated();

            // Giảm cố định → max_discount_amount tự động bằng discount_value
            if ($data['discount_type'] === 'fixed') {
                $data['max_discount_amount'] = $data['discount_value'];
            }

            $coupon = Coupon::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Thêm mã giảm giá thành công',
                'data'    => new CouponResource($coupon),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Coupon $coupon)
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Lấy thông tin mã giảm giá thành công',
                'data'    => new CouponResource($coupon),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCouponRequest $request, Coupon $coupon)
    {
        try {
            $data = $request->validated();

            // Giảm cố định → max_discount_amount tự động bằng discount_value
            if ($data['discount_type'] === 'fixed') {
                $data['max_discount_amount'] = $data['discount_value'];
            }

            $coupon->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật mã giảm giá thành công',
                'data'    => new CouponResource($coupon),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get active coupons for public display
     */
    public function getActive()
    {
        try {
            // Lấy tất cả coupon có status = true, sắp xếp theo ngày tạo mới nhất
            $coupons = Coupon::where('status', true)
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách mã giảm giá thành công',
                'data'    => CouponResource::collection($coupons),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Coupon $coupon)
    {
        try {
            $coupon->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa mã giảm giá thành công',
                'data'    => null,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
