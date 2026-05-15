<?php

namespace App\Http\Controllers;

use App\Services\GHNService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GHNController extends Controller
{
    protected GHNService $ghnService;

    public function __construct(GHNService $ghnService)
    {
        $this->ghnService = $ghnService;
    }

    public function provinces()
    {
        $data = $this->ghnService->getProvinces();
        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách tỉnh/thành công',
            'data' => $data
        ]);
    }

    public function districts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'province_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'data' => null
            ], 422);
        }

        $data = $this->ghnService->getDistricts($request->province_id);
        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách quận/huyện thành công',
            'data' => $data
        ]);
    }

    public function wards(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'district_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'data' => null
            ], 422);
        }

        $data = $this->ghnService->getWards($request->district_id);
        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách phường/xã thành công',
            'data' => $data
        ]);
    }

    public function shippingFee(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'to_district_id' => 'required|integer',
            'to_ward_code' => 'required|string',
            'weight' => 'required|integer|min:1',
            'insurance_value' => 'nullable|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'data' => null
            ], 422);
        }

        $params = [
            'to_district_id' => (int) $request->to_district_id,
            'to_ward_code' => (string) $request->to_ward_code,
            'weight' => (int) $request->weight,
            'insurance_value' => (int) ($request->insurance_value ?? 0),
        ];

        $data = $this->ghnService->calculateShippingFee($params);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tính phí ship lúc này',
                'data' => null
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tính phí ship thành công',
            'data' => $data
        ]);
    }
}
