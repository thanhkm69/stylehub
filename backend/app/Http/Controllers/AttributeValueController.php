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
        $query = AttributeValue::with('attribute');

        if ($request->has('attribute_id')) {
            $query->where('attribute_id', $request->attribute_id);
        }

        $data = $query->get();

        return response()->json([
            'status' => true,
            'message' => 'Lấy danh sách giá trị thuộc tính thành công',
            'data' => AttributeValueResource::collection($data)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAttributeValueRequest $request)
    {
        $data = $request->validated();
        $attributeValue = AttributeValue::create($data);
        return response()->json([
            'status' => true,
            'message' => 'Tạo giá trị thuộc tính thành công',
            'data' => new AttributeValueResource($attributeValue->load('attribute'))
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(AttributeValue $attributeValue)
    {
        return response()->json([
            'status' => true,
            'message' => 'Lấy giá trị thuộc tính thành công',
            'data' => new AttributeValueResource($attributeValue->load('attribute'))
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAttributeValueRequest $request, AttributeValue $attributeValue)
    {
        $data = $request->validated();
        $attributeValue->update($data);
        return response()->json([
            'status' => true,
            'message' => 'Cập nhật giá trị thuộc tính thành công',
            'data' => new AttributeValueResource($attributeValue->load('attribute'))
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AttributeValue $attributeValue)
    {
        $attributeValue->delete();
        return response()->json([
            'status' => true,
            'message' => 'Xóa giá trị thuộc tính thành công',
            'data' => new AttributeValueResource($attributeValue->load('attribute'))
        ]);
    }
}
