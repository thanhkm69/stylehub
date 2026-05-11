<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tài khoản StyleHub của bạn</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f4f4f4; padding:20px;">

    <div style="max-width:600px; margin:auto; background:white; padding:30px; border-radius:10px;">

        <!-- Logo -->
        <h2 style="text-align:center; color:#333;">StyleHub</h2>

        <p>Xin chào <b>{{ $name }}</b>,</p>

        <p>Tài khoản của bạn đã được tạo bởi Admin. Dưới đây là thông tin đăng nhập:</p>

        <!-- Email -->
        <div style="background:#f9f9f9; border:1px solid #eee; border-radius:6px; padding:16px 24px; margin:24px 0;">
            <p style="margin:0 0 8px 0; font-size:13px; color:#888;">Email đăng nhập</p>
            <p style="margin:0; font-size:16px; font-weight:bold; color:#333;">{{ $email }}</p>
        </div>

        <!-- Password -->
        <p>Mật khẩu của bạn:</p>
        <div style="text-align:center; margin:24px 0;">
            <span style="
                font-size:28px;
                font-weight:bold;
                letter-spacing:5px;
                color:#ff4d4f;
            ">
                {{ $password }}
            </span>
        </div>

        <p style="color:#e74c3c; font-weight:bold;">
            ⚠️ Vui lòng đổi mật khẩu ngay sau khi đăng nhập lần đầu.
        </p>

        <p>Nếu bạn không yêu cầu tạo tài khoản này, vui lòng liên hệ với chúng tôi ngay.</p>

        <hr>

        <p style="font-size:12px; color:#888;">
            © {{ date('Y') }} StyleHub. All rights reserved.
        </p>

    </div>

</body>
</html>