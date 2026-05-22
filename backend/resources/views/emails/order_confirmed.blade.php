<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 20px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 24px; text-transform: uppercase; letter-spacing: 2px; }
        .order-info { margin-bottom: 30px; }
        .order-info h2 { font-size: 18px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { text-align: left; padding: 10px; background: #f8f8f8; border-bottom: 1px solid #eee; }
        td { padding: 10px; border-bottom: 1px solid #eee; font-size: 14px; }
        .total-row td { font-weight: bold; font-size: 16px; border-top: 2px solid #000; }
        .footer { text-align: center; font-size: 12px; color: #999; margin-top: 30px; }
        .badge { background: #000; color: #fff; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>StyleHub</h1>
            <p>Cảm ơn bạn đã đặt hàng!</p>
        </div>

        <div class="order-info">
            <h2>Thông tin đơn hàng #{{ $order->order_code }}</h2>
            <p><strong>Ngày đặt:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
            <p><strong>Trạng thái:</strong> <span class="badge">{{ $statusLabel }}</span></p>
            <p><strong>Phương thức thanh toán:</strong> 
                @if($order->payment_method === 'cod')
                    Tiền mặt (COD)
                @elseif($order->payment_method === 'momo')
                    Ví MoMo
                @elseif($order->payment_method === 'vnpay')
                    Ví VNPay
                @else
                    {{ strtoupper($order->payment_method) }}
                @endif
            </p>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 60px;">Ảnh</th>
                    <th>Sản phẩm</th>
                    <th class="text-center">SL</th>
                    <th class="text-right">Giá</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderDetails as $detail)
                <tr>
                    <td style="width: 60px; padding: 10px 5px; vertical-align: middle;">
                        @php
                            $filename = $detail->productVariant?->image ?: $detail->product?->thumbnail;
                            $imagePath = null;
                            if ($filename) {
                                try {
                                    $imagePath = \Illuminate\Support\Facades\Storage::disk('public')->path($filename);
                                } catch (\Exception $e) {
                                    $imagePath = null;
                                }
                            }
                            $embeddedUrl = null;
                            if ($imagePath && file_exists($imagePath)) {
                                try {
                                    $embeddedUrl = $message->embed($imagePath);
                                } catch (\Exception $e) {
                                    $embeddedUrl = asset('storage/' . $filename);
                                }
                            } else if ($filename) {
                                $embeddedUrl = asset('storage/' . $filename);
                            }
                        @endphp
                        @if($embeddedUrl)
                            <img src="{{ $embeddedUrl }}" alt="{{ $detail->product_name }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; display: block;">
                        @else
                            <div style="width: 50px; height: 50px; background: #eee; border-radius: 4px; line-height: 50px; text-align: center; color: #999; font-size: 10px;">No image</div>
                        @endif
                    </td>
                    <td style="vertical-align: middle;">
                        <div style="font-weight: bold;">{{ $detail->product_name }}</div>
                        @if($detail->variant_name)
                        <div style="font-size: 11px; color: #666;">
                            Phân loại: {{ $detail->variant_name }}
                        </div>
                        @endif
                    </td>
                    <td class="text-center" style="vertical-align: middle;">{{ $detail->quantity }}</td>
                    <td class="text-right" style="vertical-align: middle;">{{ number_format($detail->price, 0, ',', '.') }}đ</td>
                </tr>
                @endforeach
                
                <tr>
                    <td colspan="3" class="text-right" style="color: #666; padding-top: 20px;">Tạm tính:</td>
                    <td class="text-right" style="padding-top: 20px;">{{ number_format($order->subtotal_amount, 0, ',', '.') }}đ</td>
                </tr>
                @if($order->discount_amount > 0)
                <tr>
                    <td colspan="3" class="text-right" style="color: #666;">Giảm giá:</td>
                    <td class="text-right">-{{ number_format($order->discount_amount, 0, ',', '.') }}đ</td>
                </tr>
                @endif
                <tr>
                    <td colspan="3" class="text-right" style="color: #666;">Phí vận chuyển:</td>
                    <td class="text-right">+{{ number_format($order->shipping_fee, 0, ',', '.') }}đ</td>
                </tr>
                <tr class="total-row">
                    <td colspan="3" class="text-right">TỔNG CỘNG:</td>
                    <td class="text-right" style="color: #000;">{{ number_format($order->total_amount, 0, ',', '.') }}đ</td>
                </tr>
            </tbody>
        </table>

        <div class="order-info">
            <h2>Địa chỉ giao hàng</h2>
            <p><strong>Người nhận:</strong> {{ $order->shipping_name }}</p>
            <p><strong>SĐT:</strong> {{ $order->shipping_phone }}</p>
            <p><strong>Địa chỉ:</strong> {{ $order->shipping_full_address }}</p>
            @if($order->note)
            <p><strong>Ghi chú:</strong> {{ $order->note }}</p>
            @endif
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} StyleHub Store. All rights reserved.</p>
            <p>Email: support@stylehub.com | Website: stylehub.com</p>
        </div>
    </div>
</body>
</html>
