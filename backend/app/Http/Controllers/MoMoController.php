<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Services\MoMoService;
use App\Http\Requests\CreatePaymentRequest;
use App\Http\Resources\PaymentTransactionResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class MoMoController extends Controller
{
    protected MoMoService $momoService;

    public function __construct(MoMoService $momoService)
    {
        $this->momoService = $momoService;
    }

    /**
     * POST api/momo/create-payment
     * Tạo URL thanh toán MoMo cho đơn hàng
     */
    public function createPayment(CreatePaymentRequest $request): JsonResponse
    {
        try {
            $order = Order::findOrFail($request->order_id);

            // 1. Kiểm tra đơn hàng thuộc về user đang đăng nhập
            if ($order->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền thanh toán cho đơn hàng này.',
                    'data' => null
                ], 403);
            }

            // 2. Kiểm tra trạng thái thanh toán của đơn hàng (unpaid / pending)
            if (!in_array($order->payment_status, ['unpaid', 'pending'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đơn hàng này đã được thanh toán hoặc đã hủy.',
                    'data' => null
                ], 400);
            }

            // 3. Kiểm tra phương thức thanh toán phải là momo
            if ($order->payment_method !== 'momo') {
                return response()->json([
                    'success' => false,
                    'message' => 'Phương thức thanh toán của đơn hàng không phải là MoMo.',
                    'data' => null
                ], 400);
            }

            DB::beginTransaction();

            $requestId = (string) Str::uuid();

            // 4. Tạo bản ghi giao dịch (PaymentTransaction)
            $transaction = PaymentTransaction::create([
                'order_id' => $order->id,
                'request_id' => $requestId,
                'order_id_momo' => $order->order_code . '_' . time(),
                'txn_ref' => $order->order_code, // fallback hoặc để cho đồng nhất
                'amount' => $order->total_amount,
                'status' => 'pending',
            ]);

            // 5. Gọi Service lấy URL thanh toán
            $payUrl = $this->momoService->createPaymentUrl($order, $requestId);

            if (!$payUrl) {
                throw new Exception("Không thể kết nối đến hệ thống MoMo.");
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tạo liên kết thanh toán MoMo thành công.',
                'data' => [
                    'payment_url' => $payUrl
                ]
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('MoMo Create Payment Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * GET api/momo/return
     * Phản hồi callback từ MoMo (Redirect Client)
     */
    public function return(Request $request): RedirectResponse
    {
        $params = $request->all();
        $requestId = $request->input('requestId');
        $orderIdMomo = $request->input('orderId'); // orderId gửi sang momo
        $resultCode = $request->input('resultCode');
        $transId = $request->input('transId');
        
        $status = 'failed';
        $message = 'Thanh toán thất bại hoặc chữ ký không hợp lệ.';

        // Lấy mã đơn hàng gốc từ orderId gửi sang MoMo (orderCode_time)
        $orderCodeParts = explode('_', $orderIdMomo);
        $orderCode = $orderCodeParts[0] ?? '';

        try {
            // 1. Xác thực chữ ký số
            if ($this->momoService->verifyReturn($params)) {
                $order = Order::where('order_code', $orderCode)->first();
                $transaction = PaymentTransaction::where('request_id', $requestId)->first();

                if ($order && $transaction) {
                    DB::beginTransaction();

                    $transaction->transaction_id = $transId;
                    $transaction->result_code = $resultCode;
                    $transaction->raw_response = $params;

                    // Mã phản hồi thành công là "0"
                    if ($resultCode == 0) {
                        $status = 'success';
                        $transaction->status = 'success';
                        $transaction->paid_at = now();

                        $order->payment_status = 'paid';
                        $order->save();
                    } else {
                        $transaction->status = 'failed';
                        
                        $order->payment_status = 'failed';
                        $order->save();
                    }

                    $message = $this->momoService->getResponseMessage((string) $resultCode);
                    $transaction->message = $message;
                    $transaction->save();
                    DB::commit();
                }
            } else {
                Log::warning('MoMo Return Signature Invalid for RequestId: ' . $requestId);
            }
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('MoMo Return Callback Error: ' . $e->getMessage());
        }

        // Redirect về frontend
        $frontendRedirectUrl = env('MOMO_FRONTEND_REDIRECT_URL', 'http://localhost:5173/payment/momo/return'); 
        // Note: URL frontend tuỳ chỉnh ở .env
        $redirectUrl = $frontendRedirectUrl . '?' . http_build_query([
            'status' => $status,
            'order_code' => $orderCode,
            'message' => $message
        ]);

        return redirect()->to($redirectUrl);
    }

    /**
     * POST api/momo/notify
     * MoMo gọi ngầm cập nhật trạng thái đơn hàng (IPN)
     */
    public function notify(Request $request): JsonResponse
    {
        $params = $request->all();
        $requestId = $request->input('requestId');
        $orderIdMomo = $request->input('orderId');
        $resultCode = $request->input('resultCode');
        $amount = (float) $request->input('amount');
        $transId = $request->input('transId');

        try {
            // 1. Xác thực chữ ký số từ MoMo IPN
            if (!$this->momoService->verifyNotify($params)) {
                return response()->json([
                    'message' => 'Invalid signature'
                ], 400); // Bad Request
            }

            // 2. Tìm giao dịch và đơn hàng
            $transaction = PaymentTransaction::where('request_id', $requestId)->first();
            
            $orderCodeParts = explode('_', $orderIdMomo);
            $orderCode = $orderCodeParts[0] ?? '';
            $order = Order::where('order_code', $orderCode)->first();

            if (!$transaction || !$order) {
                return response()->json([
                    'message' => 'Order not found'
                ], 404);
            }

            // 3. Đối soát số tiền giao dịch
            if ((float) $transaction->amount !== $amount) {
                return response()->json([
                    'message' => 'Invalid amount'
                ], 400);
            }

            // 4. Kiểm tra xem giao dịch đã xử lý chưa
            if ($transaction->status !== 'pending') {
                // Đã xử lý rồi, trả về 204 No Content theo chuẩn
                return response()->json(null, 204);
            }

            DB::beginTransaction();

            $transaction->transaction_id = $transId;
            $transaction->result_code = $resultCode;
            $transaction->raw_response = $params;
            $message = $this->momoService->getResponseMessage((string) $resultCode);
            $transaction->message = $message;

            if ($resultCode == 0) {
                $transaction->status = 'success';
                $transaction->paid_at = now();

                $order->payment_status = 'paid';
                $order->save();
            } else {
                $transaction->status = 'failed';
                
                $order->payment_status = 'failed';
                $order->save();
            }

            $transaction->save();
            DB::commit();

            return response()->json(null, 204);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('MoMo IPN Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * GET api/momo/status/{order}
     * Lấy trạng thái giao dịch
     */
    public function status(Order $order): JsonResponse
    {
        try {
            if ($order->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền xem trạng thái giao dịch của đơn hàng này.',
                    'data' => null
                ], 403);
            }

            $transaction = PaymentTransaction::where('order_id', $order->id)
                ->whereNotNull('request_id') // Phân biệt với VNPay (hoặc thêm type='momo' sau này)
                ->latest()
                ->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đơn hàng này chưa có giao dịch MoMo nào.',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Lấy trạng thái giao dịch thành công.',
                'data' => new PaymentTransactionResource($transaction)
            ]);

        } catch (Exception $e) {
            Log::error('MoMo Get Transaction Status Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
