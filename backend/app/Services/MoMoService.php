<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MoMoService
{
    /**
     * Tạo request thanh toán sang MoMo và trả về payUrl
     */
    public function createPaymentUrl(Order $order, string $requestId): ?string
    {
        $endpoint = config('momo.endpoint');
        $partnerCode = config('momo.partner_code');
        $accessKey = config('momo.access_key');
        $secretKey = config('momo.secret_key');
        $returnUrl = config('momo.return_url');
        $notifyUrl = config('momo.notify_url');
        $requestType = config('momo.request_type');

        $orderInfo = "Thanh toán đơn hàng " . $order->order_code;
        $amount = (string) round($order->total_amount);
        $orderId = $order->order_code . '_' . time(); // Đảm bảo orderId gửi sang MoMo là duy nhất
        $extraData = ""; // Có thể truyền thêm dữ liệu dạng base64 nếu cần

        // Tạo raw hash string theo tài liệu MoMo
        $rawHash = "accessKey=" . $accessKey .
            "&amount=" . $amount .
            "&extraData=" . $extraData .
            "&ipnUrl=" . $notifyUrl .
            "&orderId=" . $orderId .
            "&orderInfo=" . $orderInfo .
            "&partnerCode=" . $partnerCode .
            "&redirectUrl=" . $returnUrl .
            "&requestId=" . $requestId .
            "&requestType=" . $requestType;

        $signature = hash_hmac("sha256", $rawHash, $secretKey);

        $data = [
            'partnerCode' => $partnerCode,
            'partnerName' => "StyleHub",
            'storeId' => "StyleHub",
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $returnUrl,
            'ipnUrl' => $notifyUrl,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature
        ];

        try {
            $response = Http::post($endpoint, $data);
            $jsonResult = $response->json();

            if (isset($jsonResult['resultCode']) && $jsonResult['resultCode'] == 0 && isset($jsonResult['payUrl'])) {
                return $jsonResult['payUrl'];
            }

            Log::error('MoMo Create Payment Failed', $jsonResult ?? []);
            return null;
        } catch (\Exception $e) {
            Log::error('MoMo Request Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Xác thực chữ ký phản hồi từ Return URL
     */
    public function verifyReturn(array $params): bool
    {
        return $this->verifySignature($params);
    }

    /**
     * Xác thực chữ ký phản hồi từ IPN Notify
     */
    public function verifyNotify(array $params): bool
    {
        return $this->verifySignature($params);
    }

    /**
     * Phương thức nội bộ dùng chung để verify chữ ký SHA256 của MoMo
     */
    protected function verifySignature(array $params): bool
    {
        $accessKey = config('momo.access_key');
        $secretKey = config('momo.secret_key');
        $signature = $params['signature'] ?? '';

        $rawHash = "accessKey=" . $accessKey .
            "&amount=" . ($params['amount'] ?? '') .
            "&extraData=" . ($params['extraData'] ?? '') .
            "&message=" . ($params['message'] ?? '') .
            "&orderId=" . ($params['orderId'] ?? '') .
            "&orderInfo=" . ($params['orderInfo'] ?? '') .
            "&orderType=" . ($params['orderType'] ?? '') .
            "&partnerCode=" . ($params['partnerCode'] ?? '') .
            "&payType=" . ($params['payType'] ?? '') .
            "&requestId=" . ($params['requestId'] ?? '') .
            "&responseTime=" . ($params['responseTime'] ?? '') .
            "&resultCode=" . ($params['resultCode'] ?? '') .
            "&transId=" . ($params['transId'] ?? '');

        $expectedSignature = hash_hmac("sha256", $rawHash, $secretKey);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Lấy thông điệp phản hồi bằng Tiếng Việt dựa trên resultCode của MoMo
     */
    public function getResponseMessage(string $resultCode): string
    {
        $messages = [
            '0' => 'Giao dịch thành công.',
            '9000' => 'Giao dịch đã được xác nhận thành công.',
            '8000' => 'Giao dịch đang ở trạng thái cần chờ người dùng xác nhận.',
            '1001' => 'Giao dịch thanh toán thất bại do tài khoản người dùng không đủ tiền.',
            '1002' => 'Giao dịch bị từ chối do nhà phát hành tài khoản thanh toán.',
            '1003' => 'Giao dịch bị từ chối do thẻ đã bị khóa hoặc hết hạn.',
            '1004' => 'Giao dịch thất bại do số tiền thanh toán vượt quá hạn mức thanh toán của người dùng.',
            '1005' => 'Giao dịch thất bại do url hoặc QR code đã hết hạn.',
            '1006' => 'Giao dịch thất bại do người dùng đã từ chối xác nhận thanh toán.',
            '1007' => 'Giao dịch bị từ chối vì tài khoản người dùng đang ở trạng thái không hoạt động.',
            '1017' => 'Mã OTP không hợp lệ hoặc đã hết hạn.',
            '99' => 'Lỗi hệ thống không xác định từ phía MoMo.'
        ];

        return $messages[$resultCode] ?? 'Giao dịch không thành công hoặc mã lỗi không xác định.';
    }
}
