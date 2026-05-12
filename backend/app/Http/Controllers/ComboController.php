<?php

namespace App\Http\Controllers;

use App\Models\Combo;
use App\Http\Requests\StoreComboRequest;
use App\Http\Requests\UpdateComboRequest;
use App\Http\Resources\ComboResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Exception;

class ComboController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Combo::query();

            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            if ($request->filled('combo_type')) {
                $query->where('combo_type', $request->combo_type);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('starts_at') && $request->filled('ends_at')) {
                $query->where('starts_at', '>=', $request->starts_at)
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
            $query->orderBy($sort[0], $sort[1]);

            $limit = $request->input('limit', 15);
            $combos = $query->paginate((int) $limit);

            return ComboResource::collection($combos)->additional([
                'success' => true,
                'message' => 'Lấy danh sách combo thành công',
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
    public function store(StoreComboRequest $request)
    {
        try {
            $validated = $request->validated();

            if ($request->hasFile('thumbnail')) {
                $validated['thumbnail'] = $request->file('thumbnail')
                    ->store('uploads/combos', 'public');
            }

            $combo = Combo::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Thêm combo thành công',
                'data'    => new ComboResource($combo),
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
    public function show(Combo $combo)
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Lấy thông tin combo thành công',
                'data'    => new ComboResource($combo),
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
    public function update(UpdateComboRequest $request, Combo $combo)
    {
        try {
            $validated = $request->validated();

            if ($request->hasFile('thumbnail')) {
                if ($combo->thumbnail) {
                    Storage::disk('public')->delete($combo->thumbnail);
                }
                $validated['thumbnail'] = $request->file('thumbnail')
                    ->store('uploads/combos', 'public');
            }

            $combo->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật combo thành công',
                'data'    => new ComboResource($combo),
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
    public function destroy(Combo $combo)
    {
        try {
            if ($combo->thumbnail) {
                Storage::disk('public')->delete($combo->thumbnail);
            }

            $combo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa combo thành công',
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
