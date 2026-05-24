<?php

namespace App\Http\Controllers;

use App\Models\AttributeValue;
use App\Http\Requests\StoreAttributeValueRequest;
use App\Http\Requests\UpdateAttributeValueRequest;
use App\Http\Resources\AttributeValueResource;
use Illuminate\Http\Request;

class AttributeValueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = AttributeValue::with('attribute');

            if ($request->has('attribute_id')) {
                $query->where('attribute_id', $request->attribute_id);
            }

            $query->orderBy('attribute_id', 'asc');

            $data = $query->get();

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách giá trị thuộc tính thành công',
                'data' => AttributeValueResource::collection($data)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lấy danh sách giá trị thuộc tính thất bại',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAttributeValueRequest $request)
    {
        try {
            $data = $request->validated();
            $attributeValue = AttributeValue::create($data);
            return response()->json([
                'success' => true,
                'message' => 'Tạo giá trị thuộc tính thành công',
                'data' => new AttributeValueResource($attributeValue->load('attribute'))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tạo giá trị thuộc tính thất bại',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(AttributeValue $attributeValue)
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Lấy giá trị thuộc tính thành công',
                'data' => new AttributeValueResource($attributeValue->load('attribute'))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lấy giá trị thuộc tính thất bại',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAttributeValueRequest $request, AttributeValue $attributeValue)
    {
        try {
            $data = $request->validated();
            $attributeValue->update($data);
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật giá trị thuộc tính thành công',
                'data' => new AttributeValueResource($attributeValue->load('attribute'))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cập nhật giá trị thuộc tính thất bại',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AttributeValue $attributeValue)
    {
        try {
            $attributeValue->delete();
            return response()->json([
                'success' => true,
                'message' => 'Xóa giá trị thuộc tính thành công',
                'data' => new AttributeValueResource($attributeValue->load('attribute'))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Xóa giá trị thuộc tính thất bại',
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
