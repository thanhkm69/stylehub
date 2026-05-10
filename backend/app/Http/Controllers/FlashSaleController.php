<?php

namespace App\Http\Controllers;

use App\Models\FlashSale;
use App\Http\Requests\StoreFlashSaleRequest;
use App\Http\Requests\UpdateFlashSaleRequest;
use App\Http\Resources\FlashSaleResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Exception;

class FlashSaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $flashSales = FlashSale::query();

            if ($request->filled('search')) {
                $flashSales->where('name', 'like', '%' . $request->search . '%');
            }

            if ($request->filled('status')) {
                $flashSales->where('status', $request->status);
            }

            if ($request->filled('starts_at') && $request->filled('ends_at')) {
                $flashSales->where('starts_at', '>=', $request->starts_at)
                           ->where('ends_at', '<=', $request->ends_at);
            }

            $sortMap = [
                'display_asc'     => ['display', 'asc'],
                'display_desc'    => ['display', 'desc'],
                'created_at_asc'  => ['created_at', 'asc'],
                'created_at_desc' => ['created_at', 'desc'],
            ];

            $sortKey = $request->sort ?? 'created_at_desc';
            $sort = $sortMap[$sortKey] ?? $sortMap['created_at_desc'];
            $flashSales->orderBy($sort[0], $sort[1]);

            $limit = $request->input('limit', 15);
            $flashSales = $flashSales->paginate((int) $limit);

            return FlashSaleResource::collection($flashSales)->additional([
                'success' => true,
                'message' => 'Lấy danh sách flash sale thành công',
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
    public function store(StoreFlashSaleRequest $request)
    {
        try {
            $validated = $request->validated();

            if ($request->hasFile('thumbnail')) {
                $path = $request->file('thumbnail')->store('uploads/flash-sales', 'public');
                $validated['thumbnail'] = $path;
            }

            $flashSale = FlashSale::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Thêm flash sale thành công',
                'data'    => new FlashSaleResource($flashSale),
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
    public function show(FlashSale $flashSale)
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Lấy thông tin flash sale thành công',
                'data'    => new FlashSaleResource($flashSale),
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
    public function update(UpdateFlashSaleRequest $request, FlashSale $flashSale)
    {
        try {
            $validated = $request->validated();

            if ($request->hasFile('thumbnail')) {
                $path = $request->file('thumbnail')->store('uploads/flash-sales', 'public');
                if ($path && $flashSale->thumbnail) {
                    Storage::disk('public')->delete($flashSale->thumbnail);
                }
                $validated['thumbnail'] = $path;
            }

            $flashSale->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật flash sale thành công',
                'data'    => new FlashSaleResource($flashSale),
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
    public function destroy(FlashSale $flashSale)
    {
        try {
            if ($flashSale->thumbnail) {
                Storage::disk('public')->delete($flashSale->thumbnail);
            }

            $flashSale->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa flash sale thành công',
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
