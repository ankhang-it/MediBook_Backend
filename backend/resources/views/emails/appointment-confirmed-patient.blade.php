<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đặt lịch khám</title>
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
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 3px solid #10b981;
        }

        .header h1 {
            color: #10b981;
            margin: 0;
            font-size: 28px;
        }

        .success-icon {
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
            background-color: #f0fdf4;
            border-left: 4px solid #10b981;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #d1fae5;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: bold;
            color: #065f46;
        }

        .info-value {
            color: #1f2937;
            text-align: right;
        }

        .amount {
            font-size: 24px;
            color: #10b981;
            font-weight: bold;
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
            background-color: #10b981;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            font-weight: bold;
        }

        .note {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="success-icon">✅</div>
            <h1>Đặt Lịch Thành Công!</h1>
        </div>

        <div class="content">
            <p class="greeting">Kính gửi <strong>{{ $patient->name }}</strong>,</p>

            <p>Cảm ơn bạn đã đặt lịch khám tại <strong>MediBook</strong>. Lịch khám của bạn đã được xác nhận và thanh toán thành công.</p>

            <div class="info-box">
                <h3 style="margin-top: 0; color: #065f46;">📋 Thông Tin Lịch Khám</h3>

                <div class="info-row">
                    <span class="info-label">🆔 Mã lịch hẹn:</span>
                    <span class="info-value">{{ $appointment->appointment_id }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">👨‍⚕️ Bác sĩ:</span>
                    <span class="info-value">{{ $doctor->name }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">🏥 Chuyên khoa:</span>
                    <span class="info-value">{{ $doctorProfile->specialty->name ?? 'N/A' }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">📅 Ngày khám:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($appointment->schedule_time)->format('d/m/Y') }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">⏰ Giờ khám:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($appointment->schedule_time)->format('H:i') }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">💰 Phí khám:</span>
                    <span class="info-value amount">{{ number_format($payment->total_amount ?? 0) }} VNĐ</span>
                </div>

                <div class="info-row">
                    <span class="info-label">✅ Trạng thái:</span>
                    <span class="info-value" style="color: #10b981; font-weight: bold;">ĐÃ THANH TOÁN</span>
                </div>
            </div>

            <div class="note">
                <strong>📌 Lưu ý quan trọng:</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Vui lòng đến <strong>trước 15 phút</strong> để làm thủ tục</li>
                    <li>Mang theo CMND/CCCD và sổ khám bệnh (nếu có)</li>
                    <li>Nếu cần hủy lịch, vui lòng liên hệ trước <strong>24 giờ</strong></li>
                </ul>
            </div>

            <div style="text-align: center;">
                <a href="{{ config('app.url') }}" class="button">Xem Chi Tiết Lịch Hẹn</a>
            </div>
        </div>

        <div class="footer">
            <p><strong>Trung tâm Y khoa Phúc Khang</strong></p>
            <p>📞 Hotline: 1900-xxxx | 📧 Email: onlinemedibook@gmail.com</p>
            <p style="margin-top: 15px; font-size: 12px;">
                Email này được gửi tự động. Vui lòng không trả lời email này.
            </p>
        </div>
    </div>
</body>

</html>
