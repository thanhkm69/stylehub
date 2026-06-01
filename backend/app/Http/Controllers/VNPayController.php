<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class VNPayController extends Controller
{
    public function createPayment(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Cổng thanh toán VNPay đang được cấu hình hoặc bảo trì.'
        ]);
    }

    public function status(Order $order)
    {
        return response()->json([
            'success' => false,
            'message' => 'Không thể kiểm tra trạng thái giao dịch VNPay lúc này.'
        ]);
    }
}
