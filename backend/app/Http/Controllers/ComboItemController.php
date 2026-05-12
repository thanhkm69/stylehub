<?php

namespace App\Http\Controllers;

use App\Models\ComboItem;
use App\Http\Requests\StoreComboItemRequest;
use App\Http\Requests\UpdateComboItemRequest;
use App\Http\Resources\ComboItemResource;
use Illuminate\Http\Request;
use Exception;

class ComboItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = ComboItem::with(['product', 'productVariant']);

            if ($request->has('combo_id')) {
                $query->where('combo_id', $request->combo_id);
            }

            $data = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách sản phẩm trong combo thành công',
                'data'    => ComboItemResource::collection($data),
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
     * Store a newly created resource in storage.
     */
    public function store(StoreComboItemRequest $request)
    {
        try {
            $comboItem = ComboItem::create($request->validated());
            $comboItem->load(['product', 'productVariant']);

            return response()->json([
                'success' => true,
                'message' => 'Thêm sản phẩm vào combo thành công',
                'data'    => new ComboItemResource($comboItem),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
                'data'    => null,
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ComboItem $comboItem)
    {
        try {
            $comboItem->load(['product', 'productVariant']);
            return response()->json([
                'success' => true,
                'message' => 'Lấy chi tiết sản phẩm trong combo thành công',
                'data'    => new ComboItemResource($comboItem),
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
     * Update the specified resource in storage.
     */
    public function update(UpdateComboItemRequest $request, ComboItem $comboItem)
    {
        try {
            $comboItem->update($request->validated());
            $comboItem->load(['product', 'productVariant']);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật sản phẩm trong combo thành công',
                'data'    => new ComboItemResource($comboItem),
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
     * Remove the specified resource from storage.
     */
    public function destroy(ComboItem $comboItem)
    {
        try {
            $comboItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa sản phẩm khỏi combo thành công',
                'data'    => null,
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
