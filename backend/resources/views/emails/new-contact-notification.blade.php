<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo liên hệ mới</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f8f9fa; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .contact-info { background-color: #f8f9fa; padding: 15px; margin: 20px 0; border-left: 4px solid #007bff; }
        .footer { background-color: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>StyleHub - Admin</h1>
            <p>Thông báo: Có liên hệ mới từ khách hàng</p>
        </div>

        <div class="content">
            <p>Kính chào Admin,</p>

            <p>Hệ thống vừa nhận được một liên hệ mới từ khách hàng. Vui lòng xem xét và xử lý.</p>

            <div class="contact-info">
                <h3>Thông tin liên hệ:</h3>
                <p><strong>Họ tên:</strong> {{ $contact->full_name }}</p>
                <p><strong>Email:</strong> {{ $contact->email }}</p>
                <p><strong>Số điện thoại:</strong> {{ $contact->phone ?: 'Không cung cấp' }}</p>
                <p><strong>Chủ đề:</strong> {{ $contact->subject }}</p>
                <p><strong>Nội dung:</strong></p>
                <p>{{ $contact->message }}</p>
                <p><strong>Thời gian:</strong> {{ $contact->created_at->format('d/m/Y H:i') }}</p>
            </div>

            <p>Vui lòng đăng nhập vào hệ thống quản trị để xem chi tiết và xử lý liên hệ này.</p>

            <p>Trân trọng,<br>
            Hệ thống StyleHub</p>
        </div>

        <div class="footer">
            <p>&copy; 2026 StyleHub. Tất cả quyền được bảo lưu.</p>
        </div>
    </div>
</body>
</html>