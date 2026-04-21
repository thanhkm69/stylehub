<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>StyleHub OTP</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f4f4f4; padding:20px;">
    
    <div style="max-width:600px; margin:auto; background:white; padding:30px; border-radius:10px;">
        
        <!-- Logo -->
        <h2 style="text-align:center; color:#333;">StyleHub</h2>

        <p>Xin chào,</p>

        <p>Bạn vừa yêu cầu mã xác thực OTP. Vui lòng sử dụng mã bên dưới:</p>

        <!-- OTP -->
        <div style="text-align:center; margin:30px 0;">
            <span style="
                font-size:32px;
                font-weight:bold;
                letter-spacing:5px;
                color:#ff4d4f;
            ">
                {{ $otp }}
            </span>
        </div>

        <p>Mã OTP có hiệu lực trong <b>1 phút</b>. Vui lòng không chia sẻ mã này với bất kỳ ai.</p>

        <p>Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email.</p>

        <hr>

        <p style="font-size:12px; color:#888;">
            © {{ date('Y') }} StyleHub. All rights reserved.
        </p>
    </div>

</body>
</html>