<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Http\Requests\StoreAttributeRequest;
use App\Http\Requests\UpdateAttributeRequest;
use App\Http\Resources\AttributeResource;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $attributes = Attribute::withCount('values');

        if ($request->filled('search')) {
            $attributes->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $attributes->where('status', $request->status);
        }

        $sortMap = [
            'name_asc' => ['name', 'asc'],
            'name_desc' => ['name', 'desc'],
            'created_at_asc' => ['created_at', 'asc'],
            'created_at_desc' => ['created_at', 'desc'],
        ];

        $sortKey = $request->sort ?? 'created_at_desc';
        $sort = $sortMap[$sortKey] ?? $sortMap['created_at_desc'];
        $attributes->orderBy($sort[0], $sort[1]);

        if ($request->filled('limit')) {
            $attributes = $attributes->paginate((int)$request->limit);
        } else {
            $attributes = $attributes->get();
        }

        return AttributeResource::collection($attributes)->additional([
            'success' => true,
            'message' => 'Lấy danh sách thuộc tính thành công',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAttributeRequest $request)
    {
        $validated = $request->validated();
        $attribute = Attribute::create($validated);
        $attribute->loadCount('values');
        return response()->json([
            'success' => true,
            'message' => 'Thêm thuộc tính thành công',
            'data' => new AttributeResource($attribute)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Attribute $attribute)
    {
        $attribute->loadCount('values');
        return response()->json([
            'success' => true,
            'message' => 'Lấy thông tin thuộc tính thành công',
            'data' => new AttributeResource($attribute)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAttributeRequest $request, Attribute $attribute)
    {
        $validated = $request->validated();
        $attribute->update($validated);
        $attribute->loadCount('values');
        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thuộc tính thành công',
            'data' => new AttributeResource($attribute)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attribute $attribute)
    {
        $attribute->delete();
        return response()->json([
            'success' => true,
            'message' => 'Xóa thuộc tính thành công',
            'data' => null
        ], 200);
    }
}
