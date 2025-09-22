<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký thành công - Trung tâm Y khoa Phúc Khang</title>
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

        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }

        .content {
            padding: 30px;
        }

        .welcome-message {
            font-size: 18px;
            color: #10b981;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .user-info {
            background-color: #f0fdf4;
            border-left: 4px solid #10b981;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .user-info h3 {
            margin: 0 0 10px 0;
            color: #059669;
        }

        .info-item {
            margin: 8px 0;
        }

        .info-label {
            font-weight: bold;
            color: #374151;
        }

        .next-steps {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .next-steps h3 {
            margin: 0 0 15px 0;
            color: #1d4ed8;
        }

        .step {
            margin: 10px 0;
            padding-left: 20px;
            position: relative;
        }

        .step::before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #10b981;
            font-weight: bold;
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

        .contact-info {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
        }

        .btn {
            display: inline-block;
            background-color: #10b981;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 10px 0;
        }

        .btn:hover {
            background-color: #059669;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>🏥 Trung tâm Y khoa Phúc Khang</h1>
            <p>Đà Nẵng - Chăm sóc sức khỏe toàn diện</p>
        </div>

        <div class="content">
            <div class="welcome-message">
                🎉 Chào mừng bạn đến với Trung tâm Y khoa Phúc Khang!
            </div>

            <p>Xin chào <strong>{{ $user['fullname'] ?? $user['username'] }}</strong>,</p>

            <p>Chúng tôi rất vui mừng thông báo rằng tài khoản {{ $roleText }} của bạn đã được tạo thành công!</p>

            <div class="user-info">
                <h3>📋 Thông tin tài khoản</h3>
                <div class="info-item">
                    <span class="info-label">Tên đăng nhập:</span> {{ $user['username'] }}
                </div>
                <div class="info-item">
                    <span class="info-label">Email:</span> {{ $user['email'] }}
                </div>
                <div class="info-item">
                    <span class="info-label">Loại tài khoản:</span> {{ $roleText }}
                </div>
                @if(isset($user['phone']) && $user['phone'])
                <div class="info-item">
                    <span class="info-label">Số điện thoại:</span> {{ $user['phone'] }}
                </div>
                @endif
            </div>

            <div class="next-steps">
                <h3>🚀 Bước tiếp theo</h3>
                <div class="step">Đăng nhập vào hệ thống bằng thông tin tài khoản của bạn</div>
                <div class="step">Hoàn thiện thông tin hồ sơ cá nhân</div>
                @if($role === 'patient')
                <div class="step">Đặt lịch khám với các bác sĩ chuyên khoa</div>
                <div class="step">Theo dõi lịch sử khám bệnh và kết quả xét nghiệm</div>
                @else
                <div class="step">Cập nhật thông tin chuyên môn và kinh nghiệm</div>
                <div class="step">Quản lý lịch khám và bệnh nhân</div>
                @endif
            </div>

            <p>Nếu bạn có bất kỳ câu hỏi nào, đừng ngần ngại liên hệ với chúng tôi.</p>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ config('app.url') }}/login" class="btn">Đăng nhập ngay</a>
            </div>
        </div>

        <div class="footer">
            <p><strong>Trung tâm Y khoa Phúc Khang</strong></p>
            <p>📍 Địa chỉ: Đà Nẵng, Việt Nam</p>
            <div class="contact-info">
                <p>📞 Hotline: 1900-xxxx</p>
                <p>📧 Email: info@phuckhangmedical.com</p>
                <p>🌐 Website: www.phuckhangmedical.com</p>
            </div>
            <p style="margin-top: 20px; font-size: 12px; color: #9ca3af;">
                Email này được gửi tự động, vui lòng không trả lời trực tiếp.
            </p>
        </div>
    </div>
</body>

</html>
