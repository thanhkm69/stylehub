<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cảm ơn bạn đã liên hệ</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f8f9fa; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .footer { background-color: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>StyleHub</h1>
            <p>Cảm ơn bạn đã liên hệ với chúng tôi!</p>
        </div>

        <div class="content">
            <p>Kính chào {{ $contact->full_name }},</p>

            <p>Cảm ơn bạn đã gửi liên hệ đến StyleHub. Chúng tôi đã nhận được thông tin của bạn với chủ đề: <strong>{{ $contact->subject }}</strong></p>

            <p>Đội ngũ của chúng tôi sẽ xem xét và phản hồi bạn trong thời gian sớm nhất có thể, thường trong vòng 24-48 giờ.</p>

            <p>Nếu bạn có thêm thông tin hoặc câu hỏi nào khác, vui lòng liên hệ với chúng tôi qua email này.</p>

            <p>Trân trọng,<br>
            Đội ngũ StyleHub</p>
        </div>

        <div class="footer">
            <p>&copy; 2026 StyleHub. Tất cả quyền được bảo lưu.</p>
        </div>
    </div>
</body>
</html>