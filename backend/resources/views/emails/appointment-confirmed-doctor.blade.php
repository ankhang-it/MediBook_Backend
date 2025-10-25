<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo lịch khám mới</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 3px solid #3b82f6;
        }
        .header h1 {
            color: #3b82f6;
            margin: 0;
            font-size: 28px;
        }
        .notification-icon {
            font-size: 60px;
            margin-bottom: 15px;
        }
        .content {
            margin-top: 30px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #1f2937;
        }
        .info-box {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #dbeafe;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: bold;
            color: #1e40af;
        }
        .info-value {
            color: #1f2937;
            text-align: right;
        }
        .patient-box {
            background-color: #f0fdf4;
            border: 2px solid #10b981;
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #3b82f6;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            font-weight: bold;
        }
        .highlight {
            background-color: #fef3c7;
            padding: 2px 8px;
            border-radius: 3px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="notification-icon">🔔</div>
            <h1>Lịch Khám Mới</h1>
        </div>

        <div class="content">
            <p class="greeting">Kính gửi <strong>BS. {{ $doctor->name }}</strong>,</p>

            <p>Bạn có một lịch khám mới đã được xác nhận và thanh toán. Chi tiết như sau:</p>

            <div class="patient-box">
                <h3 style="margin-top: 0; color: #065f46;">👤 Thông Tin Bệnh Nhân</h3>
                <div class="info-row" style="border-bottom: none;">
                    <span class="info-label">Họ và tên:</span>
                    <span class="info-value">{{ $patient->name }}</span>
                </div>
                <div class="info-row" style="border-bottom: none;">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $patient->email }}</span>
                </div>
                @if($appointment->patient->phone)
                <div class="info-row" style="border-bottom: none;">
                    <span class="info-label">Số điện thoại:</span>
                    <span class="info-value">{{ $appointment->patient->phone }}</span>
                </div>
                @endif
            </div>

            <div class="info-box">
                <h3 style="margin-top: 0; color: #1e40af;">📋 Chi Tiết Lịch Hẹn</h3>
                
                <div class="info-row">
                    <span class="info-label">🆔 Mã lịch hẹn:</span>
                    <span class="info-value">{{ $appointment->appointment_id }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">📅 Ngày khám:</span>
                    <span class="info-value highlight">{{ \Carbon\Carbon::parse($appointment->schedule_time)->format('d/m/Y') }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">⏰ Giờ khám:</span>
                    <span class="info-value highlight">{{ \Carbon\Carbon::parse($appointment->schedule_time)->format('H:i') }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">💰 Phí khám:</span>
                    <span class="info-value">{{ number_format($payment->total_amount ?? 0) }} VNĐ</span>
                </div>

                <div class="info-row">
                    <span class="info-label">💳 Thanh toán:</span>
                    <span class="info-value" style="color: #10b981; font-weight: bold;">ĐÃ THANH TOÁN</span>
                </div>

                @if($appointment->reason)
                <div class="info-row">
                    <span class="info-label">📝 Lý do khám:</span>
                    <span class="info-value">{{ $appointment->reason }}</span>
                </div>
                @endif
            </div>

            <div style="text-align: center;">
                <a href="{{ config('app.url') }}" class="button">Xem Lịch Làm Việc</a>
            </div>
        </div>

        <div class="footer">
            <p><strong>Hệ thống MediBook</strong></p>
            <p>📞 Hotline: 1900-xxxx | 📧 Email: onlinemedibook@gmail.com</p>
            <p style="margin-top: 15px; font-size: 12px;">
                Email này được gửi tự động. Vui lòng không trả lời email này.
            </p>
        </div>
    </div>
</body>
</html>

