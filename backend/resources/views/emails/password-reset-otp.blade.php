<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mã xác thực đặt lại mật khẩu - Trung tâm Y khoa Phúc Khang</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }

        .container {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }

        .content {
            padding: 30px;
        }

        .otp-box {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            border: 2px dashed #10b981;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            margin: 30px 0;
        }

        .otp-code {
            font-size: 48px;
            font-weight: bold;
            color: #059669;
            letter-spacing: 10px;
            font-family: 'Courier New', monospace;
            margin: 20px 0;
        }

        .warning {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .warning p {
            margin: 5px 0;
            color: #92400e;
        }

        .info {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .info p {
            margin: 5px 0;
            color: #1e40af;
        }

        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }

        .footer p {
            margin: 5px 0;
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>🏥 Trung tâm Y khoa Phúc Khang</h1>
            <p>Đặt lại mật khẩu</p>
        </div>

        <div class="content">
            <p>Xin chào,</p>

            <p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản email <strong>{{ $email }}</strong>.</p>

            <p>Vui lòng sử dụng mã xác thực 4 chữ số sau để đặt lại mật khẩu của bạn:</p>

            <div class="otp-box">
                <p style="margin: 0 0 10px 0; color: #374151; font-size: 14px;">Mã xác thực của bạn:</p>
                <div class="otp-code">{{ $otp }}</div>
                <p style="margin: 10px 0 0 0; color: #6b7280; font-size: 12px;">Mã này có hiệu lực trong 10 phút</p>
            </div>

            <div class="warning">
                <p><strong>⚠️ Lưu ý bảo mật:</strong></p>
                <p>• Không chia sẻ mã này với bất kỳ ai</p>
                <p>• Mã chỉ có hiệu lực trong 10 phút</p>
                <p>• Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này</p>
            </div>

            <div class="info">
                <p><strong>ℹ️ Hướng dẫn:</strong></p>
                <p>1. Nhập mã xác thực 4 chữ số ở trên</p>
                <p>2. Nhập mật khẩu mới của bạn</p>
                <p>3. Xác nhận mật khẩu mới</p>
            </div>

            <p>Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ với chúng tôi.</p>
        </div>

        <div class="footer">
            <p><strong>Trung tâm Y khoa Phúc Khang</strong></p>
            <p>📍 Địa chỉ: Đà Nẵng, Việt Nam</p>
            <p>📞 Hotline: 1900-xxxx</p>
            <p>📧 Email: info@phuckhangmedical.com</p>
            <p style="margin-top: 20px; font-size: 12px; color: #9ca3af;">
                Email này được gửi tự động, vui lòng không trả lời trực tiếp.
            </p>
        </div>
    </div>
</body>

</html>

