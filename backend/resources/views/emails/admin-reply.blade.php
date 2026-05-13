<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trả lời liên hệ</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f8f9fa; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .reply-box { background-color: #f0f8ff; padding: 15px; margin: 20px 0; border-left: 4px solid #007bff; border-radius: 4px; }
        .original-contact { background-color: #f9f9f9; padding: 15px; margin: 20px 0; border-left: 4px solid #ddd; }
        .footer { background-color: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>StyleHub</h1>
            <p>Chúng tôi đã phản hồi liên hệ của bạn</p>
        </div>

        <div class="content">
            <p>Kính chào {{ $contact->full_name }},</p>

            <p>Cảm ơn bạn đã liên hệ với StyleHub. Dưới đây là câu trả lời từ đội ngũ của chúng tôi:</p>

            <div class="reply-box">
                <h3>Phản hồi từ Admin:</h3>
                <p>{{ $replyNote ?: 'Không có ghi chú thêm từ admin' }}</p>
            </div>

            <div class="original-contact">
                <h4>Liên hệ ban đầu của bạn:</h4>
                <p><strong>Chủ đề:</strong> {{ $contact->subject }}</p>
                <p><strong>Nội dung:</strong></p>
                <p>{{ $contact->message }}</p>
            </div>

            <p>Nếu bạn còn thắc mắc hoặc cần hỗ trợ thêm, vui lòng liên hệ lại với chúng tôi.</p>

            <p>Trân trọng,<br>
            Đội ngũ StyleHub</p>
        </div>

        <div class="footer">
            <p>&copy; 2026 StyleHub. Tất cả quyền được bảo lưu.</p>
        </div>
    </div>
</body>
</html>